<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('mp_blog_comments')};
DROP TABLE IF EXISTS {$this->getTable('mp_blog_posts_tag')};
DROP TABLE IF EXISTS {$this->getTable('mp_blog_posts_category')};
DROP TABLE IF EXISTS {$this->getTable('mp_blog_posts_store')};
DROP TABLE IF EXISTS {$this->getTable('mp_blog_posts')};
DROP TABLE IF EXISTS {$this->getTable('mp_blog_tags')};

DROP TABLE IF EXISTS {$this->getTable('mp_blog_categories_store')};
DROP TABLE IF EXISTS {$this->getTable('mp_blog_categories')};

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_blog_posts')} (
   `post_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
   `status` smallint(5) UNSIGNED NOT NULL,
   `title` varchar(255) NOT NULL ,
   `url_key` varchar(255) NOT NULL ,
   `use_comments` tinyint(11) NOT NULL,
   `short_content` text ,
   `full_content` text ,
   `posted_by` varchar(255) ,
   `meta_title` varchar(255) ,
   `meta_tags` varchar(255) ,
   `meta_description` tinytext ,
   `created_at` timestamp NOT NULL ,
   `updated_at` timestamp NOT NULL ,
   PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_blog_posts_store')} (
   `post_id` int(10) UNSIGNED NOT NULL,
   `store_id` smallint(5) UNSIGNED NOT NULL,
   KEY `FK_MP_BLOG_POST_STORE` (`post_id`),
   CONSTRAINT `FK_MP_BLOG_POST_STORE` FOREIGN KEY (`post_id`) REFERENCES `{$this->getTable('mp_blog_posts')}` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_blog_tags')} (
   `tag_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
   `name` varchar(255) NOT NULL ,
   `url_key` varchar(255) NOT NULL ,
   PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_blog_categories')} (
   `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
   `name` varchar(255) NOT NULL ,
   `url_key` varchar(255) NOT NULL ,
   `status` smallint(5) UNSIGNED NOT NULL,
   `sort_order` int(5) UNSIGNED NOT NULL default '0',
   `meta_title` varchar(255) ,
   `meta_tags` varchar(255) ,
   `meta_description` tinytext ,
   `created_at` timestamp NOT NULL ,
   `updated_at` timestamp NOT NULL ,
   PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_blog_categories_store')} (
   `category_id` int(10) UNSIGNED NOT NULL,
   `store_id` smallint(5) UNSIGNED NOT NULL,
   KEY `FK_MP_BLOG_CATEGORY_STORE` (`category_id`),
   CONSTRAINT `FK_MP_BLOG_CATEGORY_STORE` FOREIGN KEY (`category_id`) REFERENCES `{$this->getTable('mp_blog_categories')}` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_blog_posts_category')} (
   `post_id` int(10) UNSIGNED NOT NULL,
   `category_id` int(10) UNSIGNED NOT NULL,
   KEY `FK_MP_BLOG_POST_CATEGORY_POST` (`post_id`),
   KEY `FK_MP_BLOG_POST_CATEGORY_CATEGORY` (`category_id`),
   CONSTRAINT `FK_MP_BLOG_POST_CATEGORY_POST` FOREIGN KEY (`post_id`) REFERENCES `{$this->getTable('mp_blog_posts')}` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `FK_MP_BLOG_POST_CATEGORY_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES `{$this->getTable('mp_blog_categories')}` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_blog_posts_tag')} (
   `post_id` int(10) UNSIGNED NOT NULL,
   `tag_id` int(10) UNSIGNED NOT NULL,
   KEY `FK_MP_BLOG_POST_TAG_POST` (`post_id`),
   KEY `FK_MP_BLOG_POST_TAG_TAG` (`tag_id`),
   CONSTRAINT `FK_MP_BLOG_POST_TAG_POST` FOREIGN KEY (`post_id`) REFERENCES `{$this->getTable('mp_blog_posts')}` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
   CONSTRAINT `FK_MP_BLOG_POST_TAG_TAG` FOREIGN KEY (`tag_id`) REFERENCES `{$this->getTable('mp_blog_tags')}` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mp_blog_comments')}(
  `comment_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` int(10) UNSIGNED NOT NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `customer_id` INT(10) UNSIGNED,
  `status` smallint(5) UNSIGNED NOT NULL,
  `reply_to` BIGINT UNSIGNED,
  `message` TEXT,
  `name` VARCHAR(255),
  `email` VARCHAR(255),
  `session_id` VARCHAR(255),
  `created_at` timestamp NOT NULL ,
  `updated_at` timestamp NOT NULL ,
  PRIMARY KEY (`comment_id`),
  CONSTRAINT `FK_MPBLOG_COMMENT_POST` FOREIGN KEY (`post_id`) REFERENCES `{$this->getTable('mp_blog_posts')}` (`post_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_MPBLOG_COMMENT_COMMENTS` FOREIGN KEY (`reply_to`) REFERENCES `{$this->getTable('mp_blog_comments')}` (`comment_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=INNODB CHARSET=utf8;

    ");

$installer->endSetup();