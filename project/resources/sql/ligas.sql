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

CREATE TABLE `tenants` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) UNIQUE NOT NULL,
  `slug` varchar(255) UNIQUE NOT NULL,
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `users` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` integer NOT NULL,
  `language_id` integer,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50),
  `time_zone` varchar(100) NOT NULL DEFAULT 'UTC',
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (now())
);

CREATE TABLE `addresses` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` integer NOT NULL,
  `user_id` integer NOT NULL,
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
  `tenant_id` integer NOT NULL,
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
  `tenant_id` integer NOT NULL,
  `user_id` integer NOT NULL,
  `role_id` integer NOT NULL,
  `created_at` timestamp DEFAULT (now())
);

CREATE UNIQUE INDEX `admin_divisions_index_0` ON `admin_divisions` (`country_id`, `name`);

CREATE UNIQUE INDEX `admin_divisions_index_1` ON `admin_divisions` (`country_id`, `code`);

CREATE UNIQUE INDEX `municipalities_index_2` ON `municipalities` (`admin_division_id`, `name`);

CREATE UNIQUE INDEX `municipalities_index_3` ON `municipalities` (`admin_division_id`, `code`);

CREATE UNIQUE INDEX `localities_index_4` ON `localities` (`municipality_id`, `name`);

CREATE INDEX `localities_index_5` ON `localities` (`postal_code`) USING BTREE;

CREATE INDEX `users_index_6` ON `users` (`tenant_id`) USING BTREE;

CREATE UNIQUE INDEX `users_index_7` ON `users` (`tenant_id`, `email`);

CREATE INDEX `users_index_8` ON `users` (`time_zone`) USING BTREE;

CREATE INDEX `addresses_index_9` ON `addresses` (`tenant_id`) USING BTREE;

CREATE INDEX `addresses_index_10` ON `addresses` (`user_id`) USING BTREE;

CREATE INDEX `addresses_index_11` ON `addresses` (`country_id`) USING BTREE;

CREATE INDEX `addresses_index_12` ON `addresses` (`admin_division_id`) USING BTREE;

CREATE INDEX `addresses_index_13` ON `addresses` (`municipality_id`) USING BTREE;

CREATE INDEX `addresses_index_14` ON `addresses` (`locality_id`) USING BTREE;

CREATE INDEX `addresses_index_15` ON `addresses` (`postal_code`) USING BTREE;

CREATE INDEX `roles_index_16` ON `roles` (`tenant_id`) USING BTREE;

CREATE UNIQUE INDEX `roles_index_17` ON `roles` (`tenant_id`, `name`);

CREATE UNIQUE INDEX `roles_index_18` ON `roles` (`tenant_id`, `slug`);

CREATE UNIQUE INDEX `permissions_index_19` ON `permissions` (`resource`);

CREATE INDEX `role_permissions_index_20` ON `role_permissions` (`role_id`) USING BTREE;

CREATE INDEX `role_permissions_index_21` ON `role_permissions` (`permission_id`) USING BTREE;

CREATE UNIQUE INDEX `role_permissions_index_22` ON `role_permissions` (`role_id`, `permission_id`);

CREATE INDEX `user_roles_index_23` ON `user_roles` (`tenant_id`) USING BTREE;

CREATE INDEX `user_roles_index_24` ON `user_roles` (`user_id`) USING BTREE;

CREATE INDEX `user_roles_index_25` ON `user_roles` (`role_id`) USING BTREE;

CREATE UNIQUE INDEX `user_roles_index_26` ON `user_roles` (`tenant_id`, `user_id`, `role_id`);

ALTER TABLE `admin_divisions` ADD FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

ALTER TABLE `municipalities` ADD FOREIGN KEY (`admin_division_id`) REFERENCES `admin_divisions` (`id`);

ALTER TABLE `localities` ADD FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`);

ALTER TABLE `users` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `users` ADD FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`admin_division_id`) REFERENCES `admin_divisions` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`municipality_id`) REFERENCES `municipalities` (`id`);

ALTER TABLE `addresses` ADD FOREIGN KEY (`locality_id`) REFERENCES `localities` (`id`);

ALTER TABLE `roles` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `role_permissions` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

ALTER TABLE `role_permissions` ADD FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

ALTER TABLE `user_roles` ADD FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`);

ALTER TABLE `user_roles` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_roles` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
