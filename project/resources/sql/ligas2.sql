CREATE TABLE `countries` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) UNIQUE NOT NULL,
  `iso2_code` char(2) UNIQUE NOT NULL,
  `iso3_code` char(3) UNIQUE,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `admin_divisions` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `country_id` integer NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50),
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `municipalities` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `admin_division_id` integer NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50),
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `localities` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `municipality_id` integer NOT NULL,
  `name` varchar(255) NOT NULL,
  `postal_code` varchar(20),
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `languages` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) UNIQUE NOT NULL,
  `iso_code` varchar(10) UNIQUE NOT NULL,
  `native_name` varchar(255),
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `currencies` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) UNIQUE NOT NULL,
  `code` char(3) UNIQUE NOT NULL,
  `symbol` varchar(10),
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `leagues` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) UNIQUE NOT NULL,
  `slug` varchar(255) UNIQUE NOT NULL,
  `short_name` varchar(100),
  `description` text,
  `country_id` integer,
  `language_id` integer,
  `currency_id` integer,
  `address_id` integer,
  `contact_email` varchar(255),
  `contact_phone` varchar(50),
  `logo_url` varchar(500),
  `website_url` varchar(500),
  `time_zone` varchar(100) NOT NULL DEFAULT 'UTC',
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `users` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `language_id` integer,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50),
  `time_zone` varchar(100) NOT NULL DEFAULT 'UTC',
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `league_users` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `league_id` integer NOT NULL,
  `user_id` integer NOT NULL,
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `addresses` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `league_id` integer NOT NULL,
  `name` varchar(255) NOT NULL,
  `country_id` integer NOT NULL,
  `admin_division_id` integer,
  `municipality_id` integer,
  `locality_id` integer,
  `line_1` varchar(255) NOT NULL,
  `line_2` varchar(255),
  `postal_code` varchar(20),
  `reference` varchar(255),
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `roles` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `league_id` integer NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `permissions` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `resource` varchar(100) NOT NULL,
  `can_read` boolean DEFAULT false,
  `can_write` boolean DEFAULT false,
  `can_create` boolean DEFAULT false,
  `can_delete` boolean DEFAULT false,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `role_permissions` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `role_id` integer NOT NULL,
  `permission_id` integer NOT NULL,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `user_roles` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `league_user_id` integer NOT NULL,
  `role_id` integer NOT NULL,
  `created_at` timestamp DEFAULT (now())
);

CREATE UNIQUE INDEX `admin_divisions_index_0` ON `admin_divisions` (`country_id`, `name`);

CREATE UNIQUE INDEX `admin_divisions_index_1` ON `admin_divisions` (`country_id`, `code`);

CREATE UNIQUE INDEX `municipalities_index_2` ON `municipalities` (`admin_division_id`, `name`);

CREATE UNIQUE INDEX `municipalities_index_3` ON `municipalities` (`admin_division_id`, `code`);

CREATE UNIQUE INDEX `localities_index_4` ON `localities` (`municipality_id`, `name`);

CREATE INDEX `localities_index_5` ON `localities` (`postal_code`) USING BTREE;

CREATE INDEX `leagues_index_6` ON `leagues` (`name`) USING BTREE;

CREATE UNIQUE INDEX `leagues_index_7` ON `leagues` (`slug`);

CREATE INDEX `leagues_index_8` ON `leagues` (`country_id`) USING BTREE;

CREATE INDEX `leagues_index_9` ON `leagues` (`language_id`) USING BTREE;

CREATE INDEX `leagues_index_10` ON `leagues` (`currency_id`) USING BTREE;

CREATE INDEX `leagues_index_11` ON `leagues` (`address_id`) USING BTREE;

CREATE INDEX `leagues_index_12` ON `leagues` (`time_zone`) USING BTREE;

CREATE INDEX `users_index_13` ON `users` (`time_zone`) USING BTREE;

CREATE INDEX `league_users_index_14` ON `league_users` (`league_id`) USING BTREE;

CREATE INDEX `league_users_index_15` ON `league_users` (`user_id`) USING BTREE;

CREATE UNIQUE INDEX `league_users_index_16` ON `league_users` (`league_id`, `user_id`);

CREATE INDEX `addresses_index_17` ON `addresses` (`league_id`) USING BTREE;

CREATE INDEX `addresses_index_18` ON `addresses` (`name`) USING BTREE;

CREATE INDEX `addresses_index_19` ON `addresses` (`country_id`) USING BTREE;

CREATE INDEX `addresses_index_20` ON `addresses` (`admin_division_id`) USING BTREE;

CREATE INDEX `addresses_index_21` ON `addresses` (`municipality_id`) USING BTREE;

CREATE INDEX `addresses_index_22` ON `addresses` (`locality_id`) USING BTREE;

CREATE INDEX `addresses_index_23` ON `addresses` (`postal_code`) USING BTREE;

CREATE INDEX `roles_index_24` ON `roles` (`league_id`) USING BTREE;

CREATE UNIQUE INDEX `roles_index_25` ON `roles` (`league_id`, `name`);

CREATE UNIQUE INDEX `roles_index_26` ON `roles` (`league_id`, `slug`);

CREATE UNIQUE INDEX `permissions_index_27` ON `permissions` (`resource`);

CREATE INDEX `role_permissions_index_28` ON `role_permissions` (`role_id`) USING BTREE;

CREATE INDEX `role_permissions_index_29` ON `role_permissions` (`permission_id`) USING BTREE;

CREATE UNIQUE INDEX `role_permissions_index_30` ON `role_permissions` (`role_id`, `permission_id`);

CREATE INDEX `user_roles_index_31` ON `user_roles` (`league_user_id`) USING BTREE;

CREATE INDEX `user_roles_index_32` ON `user_roles` (`role_id`) USING BTREE;

CREATE UNIQUE INDEX `user_roles_index_33` ON `user_roles` (`league_user_id`, `role_id`);

ALTER TABLE `admin_divisions` ADD FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

ALTER TABLE `municipalities` ADD FOREIGN KEY (`admin_division_id`) REFERENCES `admin_divisions` (`id`);

ALTER TABLE `localities` ADD FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`);

ALTER TABLE `leagues` ADD FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

ALTER TABLE `leagues` ADD FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`);

ALTER TABLE `leagues` ADD FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`);

ALTER TABLE `leagues` ADD FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`);

ALTER TABLE `league_users` ADD FOREIGN KEY (`league_id`) REFERENCES `leagues` (`id`);

ALTER TABLE `league_users` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `users` ADD FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`league_id`) REFERENCES `leagues` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`admin_division_id`) REFERENCES `admin_divisions` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`locality_id`) REFERENCES `localities` (`id`);

ALTER TABLE `roles` ADD FOREIGN KEY (`league_id`) REFERENCES `leagues` (`id`);

ALTER TABLE `role_permissions` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

ALTER TABLE `role_permissions` ADD FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

ALTER TABLE `user_roles` ADD FOREIGN KEY (`league_user_id`) REFERENCES `league_users` (`id`);

ALTER TABLE `user_roles` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
