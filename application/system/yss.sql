/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50085
 Source Host           : localhost
 Source Database       : yss

 Target Server Type    : MySQL
 Target Server Version : 50085
 File Encoding         : iso-8859-1

 Date: 06/20/2010 18:06:46 PM
*/

SET NAMES latin1;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `company`
-- ----------------------------
DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `company_user`
-- ----------------------------
DROP TABLE IF EXISTS `company_user`;
CREATE TABLE `company_user` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `domain` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `password` char(64) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

