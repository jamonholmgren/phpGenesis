/*
Navicat MySQL Data Transfer

Source Server         : CONCORD
Source Server Version : 50141
Source Host           : csdrouter.clearsightdesign.com:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50141
File Encoding         : 65001

Date: 2011-06-01 15:23:39
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `site_settings`
-- ----------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `options` text,
  `value` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of site_settings
-- ----------------------------
