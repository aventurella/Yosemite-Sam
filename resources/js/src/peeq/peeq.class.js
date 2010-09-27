function peeq() 
{	
	var sammy,
		TEMPLATE_PATH = "/resources/templates/";
	
	// PRIVATE --------------------------------
	var get_template_path = function(template_name)
	{
		return TEMPLATE_PATH + template_name + ".template";
	};
	
	// get each page's information
	var get_page_info = function(page_name) 
	{
		var account_domain = "blitz"; //get_subdomain();
		var page_props = get_page_props();
		
		var pages = {
			project: {
				storage_key: [account_domain, "projects"],
				body_id: ""
			},
			view: {
				storage_key: [account_domain, page_props.project.replace("-", "_"), "views"],
				body_id: ""
			},
			state: {
				storage_key: [account_domain, page_props.project.replace("-", "_"), page_props.view.replace("-", "_"), "states"],
				body_id: ""
			},
			annotate: {
				storage_key: [account_domain, page_props.project.replace("-", "_"), page_props.view.replace("-", "_"), page_props.state.replace("-", "_"), "annotate"],
				body_id: "annotate"
			},
			settings: {
				storage_key: [account_domain, "settings"],
				body_id: ""
			}
		};
		
		var page = (pages[page_name]) ? pages[page_name] : null;
		
		if(page)
		{
			page.storage_key = page.storage_key.join(".");
		}
		
		return page;
	};
	
	var get_subdomain = function()
	{
		return document.location.host.split(".")[0];		
	};
	
	// split hash into page properties
	var get_page_props = function()
	{
		// blitz.yss.com/#/lucy-the-dog/homepage/logged-in/
		var ary_hash = document.location.hash.split("/"),
			len = ary_hash.length,
			props = {
				project: "",
				view: "",
				state: ""
			};
		
		if(len)
		{
			if(len > 1)
			{
				props.project = ary_hash[1];
			}
			
			if(len > 2)
			{
				props.view = ary_hash[2];
			}
			
			if(len > 3)
			{
				props.state = ary_hash[3];
			}
		}
		
		return props;
	};
	
	// swap transition
	var swap_transition = function(content, transition_out_callback, complete_callback) 
	{		
		sammy.$element().stop(false, true).animate({
			"opacity": 0
		}, 200, "linear", function() {
			$(this).html(content);
			if($.isFunction(transition_out_callback)) 
			{
				transition_out_callback(this);
				$(this).animate({
					"opacity": 1
				}, 300, "linear", function() {
					if($.isFunction(complete_callback)) 
					{
						complete_callback(this);
					}
				});
			}
		});
	};
	
	var setup_routes = function() 
	{
		sammy = $.sammy("#main", function() {
			/// turn off logging
            //Sammy.log = this.log = function() {};
            
            // plugins
            this.use(Sammy.Session);
			this.use(Sammy.Template);
			this.use(Sammy.Title);

			// 404
			this.notFound = function(verb, path)
			{
				window.location = "/";
				return true;
			};
			
			// set title
			this.setTitle("peeq | BLITZ");

			// ROUTES
			// projects
			this.get("", function(context) {
				change_nav();
				var page = get_page_info("project");
				
				peeq.api.request("/handler", {"service": "project"}, "get", function(data) {
					var data = data.result || {};

					/*
					// if offline, try to grab from local storage, or fallbacks to cookie, or in-memory storage
					if(!peeq.is_online)
					{
						data = context.session(page.storage_key, function() {
							return {};
						});					
					}
					*/
					
					var template = (data.length) ? "projects" : "projects-none";			
					
					context.render(get_template_path(template), {"projects": data}, function(content) {
						swap_transition(content, function() {
							$(".pie-chart").piechart();
							$("body").attr("id", page.body_id);
							setup_modals();

							$(".search").philter({
								query_over: ".paginate li",
								query_by: ".title"
							}).bind("complete.philter", function(evt, matches) {
								$(".paginate li.last").removeClass("last");
								if(matches > 0)
								{
									$(".no-matches").hide();
									var txt_matches = matches > 1 ? "matches" : "match";
									$(".matches").html('<span class="complete">' + matches + '</span> ' + txt_matches);
									$(".paginate li:visible:last").addClass("last");
								}
								else if(matches == 0)
								{
									$(".no-matches").show();
									$(".matches").text("");									
								}
								else
								{
									$(".no-matches").hide();
									$(".matches").text("");	
								}
							}).parents("form").submit(function() {
								return false;
							});
							
							$("input").toggle_form_field();
							$(".excerpt").each(function() {
								$(this).html($(this).html().replace(/&lt;br \/&gt;/g, "<br />"));	
							});
							
							// store data
							context.session(page.storage_key, data);
						});
					});				
				});		
			})
			// views
			.get("#/:project", function(context) {
				change_nav();
				var page = get_page_info("view");
				
				// settings page
				if(context.params["project"].toLowerCase() == "settings")
				{
					this.trigger("settings", context);
				}
				else // view
				{												
					peeq.api.request("/handler", {"service": "view", "project": context.params["project"]}, "get", function(data) {	
						var data = data.result || {};

						/*
						// if offline, try to grab from local storage, or fallbacks to cookie, or in-memory storage
						if(!peeq.is_online)
						{						
							data = context.session(page.storage_key, function() {
								return {};
							});					
						}
						*/

						if(!data.project)
						{
							context.notFound();
						}

						var template = (data.views.length && data.views[0]) ? "views" : "views-none";			
						
						context.render(get_template_path(template), {"views": data.views, "project": data.project}, function(content) {									 
							swap_transition(content, function() {											
								change_bg("views");									
								// all pie charts in view except #pie-chart-project
								$(".pie-chart:not(#pie-chart-project)").piechart({
									radius: 10,
									xpos: 10,
									ypos: 10,
									width: 20,
									height: 20,
									show_labels: false,
									is_hoverable: false
								});
				
								$("#pie-chart-project").piechart({
									radius: 60,
									xpos: 90,
									ypos: 90,
									width: 190,
									height: 170
								});
				
								$("body").attr("id", page.body_id);

								$(".search").philter({
									query_over: ".paginate li",
									query_by: ".title"
								}).bind("complete.philter", function(evt, matches) {
									$(".paginate li.last").removeClass("last");
									if(matches > 0)
									{
										$(".no-matches").hide();
										var txt_matches = matches > 1 ? "matches" : "match";
										$(".matches").html('<span class="complete">' + matches + '</span> ' + txt_matches);
										$(".paginate li:visible:last").addClass("last");
									}
									else if(matches == 0)
									{
										$(".no-matches").show();
										$(".matches").text("");									
									}
									else
									{
										$(".no-matches").hide();
										$(".matches").text("");	
									}
								}).parents("form").submit(function() {
									return false;
								});
						
								$("input").toggle_form_field();
						
								// download attachments (in new window)
								$("#table-attachments tbody").delegate("tr", "click", function() {
									var attachment_id = $(this).find(".btn-delete").attr("href").substr(1);
									window.open("/api/attachments/project" + encodeURIComponent("/" + context.params["project"] + "/attachment/" + attachment_id));
								});
																								
								setup_modals();
								peeq.editable.main();
								
								// store data
								//context.session(page.storage_key, data);
							});
						});	
					});
				}		
			})
			.get("#/:project/:view", function(context) {
				// redirect to project
				this.redirect("");
			})
			// states
			.get("#/:project/:view/:state", function(context) {
				change_nav();
				var page = get_page_info("state");
											
				peeq.api.request("/handler", {"service": "state", "project": context.params["project"], "view": context.params["view"], "state": context.params["state"]}, "get", function(data) {								
					var data = data.result || {};
						//storage_key = "blitz." + context.params["project"] + "-" + context.params["view"] + "-states";
					
					var current_state = peeq.utils.template.states.get_current(data.states, context.path.replace("#", "project"));
										
					/*
					// if offline, try to grab from local storage, or fallbacks to cookie, or in-memory storage
					if(!peeq.is_online)
					{						
						data = context.session(storage_key, function() {
							return {};
						});					
					}
					*/
										
					context.render(get_template_path("states"), {"view": data.view, "state": current_state, "annotations": data.annotations}, function(content) {
						swap_transition(content, function() {										
							change_bg("states");							
							$("#pie-chart-project").piechart({
								radius: 60,
								xpos: 90,
								ypos: 90,
								width: 190,
								height: 170
							});
			
							$("body").attr("id", "");
							setup_modals();
							peeq.editable.main();
						
							// change states
							$("#table-states tbody").delegate("tr:not(.current)", "click", function() {
								var state_id = $(this).find(".btn-delete").attr("href").substr(1);
								document.location.href = "#/" + context.params["project"] + "/" + context.params["view"] + "/" + state_id;
							});
						
							$("#table-annotations-container").find(".table-sortable").tablesorter({
								"cssAsc": "icon-sort-asc",
								"cssDesc": "icon-sort-desc"
							});
						
							// clicking on tr fire annotation in preview's click event (deeplinking into annotation)
							$("#table-annotations-container").delegate(".annotation-item", "click", function() {
								var annotation_id = peeq.utils.template.annotations.get_id_from_elt($(this));
								$(".annotate-preview-container ." + annotation_id).click();
							});
							
													
							// preview							
							var preview_annotation = null,
							  	preview_position = null,
								$annotation_preview_image = $("#annotate-preview-image"),
								preview_width = $annotation_preview_image.width(),
								preview_height = $annotation_preview_image.height();
																		
							$("#annotate-preview").addAnnotations(function(props) {
								return $("<a />", {
									"href": document.location.href + "/annotate:" + peeq.utils.template.annotations.sanitize_id(props._id),
									"class": "annotation-item annotation-id-" + peeq.utils.template.annotations.sanitize_id(props._id) +  " icon " + peeq.utils.template.get_annotation_marker_class(props) + " ir" ,
									"html": props.type
								});												
							}, data.annotations, {"containerHeight": preview_height});
							
							// hide preview annotation positioned outside preview image
							for(var i = 0, len = data.annotations.length, $preview_annotations = $("#annotate-preview").find(".annotation-item"); i < len; i++)
							{
								$preview_annotation = $($preview_annotations[i]);
								preview_position = $preview_annotation.position();
								
								if(preview_position.top < -5 || preview_position.top > preview_height ||
								   preview_position.left < -5 || preview_position.left > preview_width)
								{
									$preview_annotation.css("visibility", "hidden");
								}			
							}
							
							
							$("#main").delegate(".annotation-item", "mouseover", function() {
								var annotation_id = peeq.utils.template.annotations.get_id_from_elt($(this));
								$("." + annotation_id).addClass("on")
							}).delegate(".annotation-item", "mouseout", function() {
								$(".annotation-item.on").removeClass("on");
							}).delegate(".annotation-item", "click", function() {
								if($(this).attr("href"))
								{
									document.location.href = $(this).attr("href");
								}
							});																		
							
							// store data
							//context.session(storage_key, data);
						});
					});
				});
			})
			// annotate
			.get("#/:project/:view/:state/annotate", function(context) {
				this.trigger("annotate", context);
			})
			// annotate w/ deeplinking
			.get("#/:project/:view/:state/annotate::id", function(context) {
				this.trigger("annotate", context);
			});
			
			// settings view
			this.bind("settings", function(event, context) {
				change_nav();		
				
				context.render(get_template_path("settings"), {"users": "test"}, function(content) {
					swap_transition(content, function() {
						change_bg("settings");
						setup_modals();
				        $("body").attr("id", "");
			
						$(".settings").find(".table-sortable").tablesorter({
							"cssAsc": "icon-sort-asc",
							"cssDesc": "icon-sort-desc"
						});
					});
				});
			});
			
			// annotate event
			// handles annotate view and deeplinking to an annotation in annotate view
			this.bind("annotate", function(event, context) {
				change_nav();
				
				peeq.api.request("/handler", {"service": "annotate", "project": context.params["project"], "view": context.params["view"], "state": context.params["state"]}, "get", function(data) {
					var data = data.result || {};											

					context.render(get_template_path("annotate"), data.state, function(content) {
						swap_transition(content, function() {
							change_bg("annotate");
							$("body").attr("id", "annotate");
					
							// handle annotate methods once representation has loaded		
							var	$representation = $("#representation");										
							$representation.find("img:eq(0)").load(function() {
								// size representation
								$representation.width($(this)[0].naturalWidth).height($(this)[0].naturalHeight);
							
					
								// back to details button
								$("header .btn-back").attr("href", "#/" + context.params["project"] + "/" + context.params["view"] + "/" + context.params["state"]).click(function() {
									// clean up from annotate view
									peeq.annotate.cleanup();
								});
					
								peeq.annotate.config.users = {
									"alincoln": "alincoln - Project Manager | BLITZ",
									"jmadison": "jmadison - Software Developer | BLITZ",
									"ajackson": "ajackson - Art Director | BLITZ",
									"bross": "bross - Designer | BLITZ"
								};
							
								var groups = {"0": "None"};
								if(data.task_groups)
								{
									for(var i = 0, len = data.task_groups.groups.length; i < len; i++)
									{
										groups[data.task_groups.groups[i]._id] = data.task_groups.groups[i].label;
									}											
								}
							
								groups["-1"] = "Create New Group";
								peeq.annotate.config.task_groups = $.extend(peeq.annotate.config.task_groups, groups);
							
								peeq.annotate.main();
						
								var deeplink_id = context.params["id"] || null;
								peeq.annotate.add_annotations(data.annotations, deeplink_id);						
							});
						});
					});
				});
			});
		});
	};
	
	var change_nav = function() 
	{
		var hash = document.location.hash,
			$header = $("header");
			
		$header.find("a").removeClass("off");
		$header.find("a[href=" + hash + "]").addClass("on");
	};
	
	var transition_in_footer = function()
	{
		$("footer").css({
			"top": "+=10"
		}).delay(250).animate({
			"top": "-=10",
			"opacity": 1
		}, 250, "easeOutQuad");
		
	};	
	
	// checking every 500ms for network connection
	var poll_network_connectivity = function() 
	{
		$.polling(200, function(check_again) {
			if(this.is_online != navigator.onLine)
			{
				this.is_online = navigator.onLine;
				if(navigator.onLine) // online => hide #network-connectivity
				{
					$("#network-connectivity:visible").animate({
						"bottom": "-30px"
					}, 200, "easeOutBack");
				}
				else // offline => show #network-connectivity
				{
					$("#network-connectivity").animate({
						"bottom": "-3px"
					}, 200, "easeOutBack");
				}
			}
			check_again();
		});
	};
	
	var change_bg = function(id)
	{
		$("#bg img:not(#bg-default):visible").animate({
			"opacity": 0
		}, 400);
		
		if(id)
		{
			$("#bg img[src$=" + id + ".png]").animate({
				"opacity": 1
			}, 400);
		}
	}
	
	
	// registers modal, add, delete events
	var register_events = function() 
	{		
		// forms
		try {
			peeq.forms.main();
		} catch(e) {}
		
		// expander
		$("#main").delegate(".expander", "click", function() {
			$(this).parents(".column").toggleClass("wide");
		});
		
		// logout
		$("#btn-logout").click(function() {
			$.post("/api/account/logout", function(response) {
				// redirect 
				document.location.href = "http://yss.com";
			});
		})
	};
	
	// setup modals
	var setup_modals = function() 
	{
		$(".modal").addClass("jqmWindow").jqm({
			overlay: 90,
			trigger: false,
			closeClass: "btn-modal-close",
			onShow: function(hash) {
				hash.w.css({
					"top": "-1000px",
					"display": "block",
					"opacity": 0
				}).animate({
					"top": "12%",
					"opacity": 1
				}, 300, "easeOutQuad");
				
				$("input").toggle_form_field();
				$("textarea").toggle_form_field();
			},
			onHide: function(hash) {
				hash.w.animate({
					"top": "-1000px",
					"opacity": 0
				}, 150, "easeOutQuad");
				
				hash.o.fadeOut(150, function() {
					$(this).remove();
				});
			}
		});	
		
		$(".btn-modal").click(function() {
			$(".modal"+ get_modal_view(this)).jqmShow();
			
			// attachment delete modal link 
			// state delete modal link
			// => populate fields
			if($(this).hasClass("modal-view-delete-attachment") || $(this).hasClass("modal-view-delete-state"))
			{
				var $this = $(this),
					id = $this.attr("href").substr(1),
					$frm = $this.hasClass("modal-view-delete-attachment") ? $("#frm-attachment-delete") :  $("#frm-state-delete");
					
				$frm.find("input[name=id]").val(id); // populate id
				$frm.find("p strong").append(" " + $this.parents("tr").find(".table-column-title").text()); // populate label
			}
			
			return false;
		});
		
		function get_modal_view(btn) 
		{
			var regExp = /modal-view-(\w|\d|-)*/,
				class_names = $(btn)[0].className,
				matches = class_names.match(regExp);
			return (matches.length) ? "." + matches[0] : "";
		}
		
		// cancel triggers close
		$(".btn-modal-cancel").click(function() {
			$(".btn-modal-close").click();
			return false;
		});
		
		// file upload skin
		$("input[type=file]").filestyle({ 
	   		image: "/resources/imgs/btn-browse.png",
		    imageheight : 50,
		    imagewidth : 87,
		    width : 260	
		});
	};
	
	// PUBLIC --------------------------------
	this.is_online = navigator.onLine;
		
	this.main = function() 
	{
		// setup routes
		setup_routes();
		// run sammy
		sammy.run();
		
		register_events();
		
		// transition in footer
		transition_in_footer();
		
		// setup polling for online/offline connectivity
		poll_network_connectivity();
	};
		
	this.toString = function()
	{
		return "No peeqing!";
	};
};