SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` tinyint(2) DEFAULT NULL,
  `json` longtext CHARACTER SET utf8mb4 DEFAULT NULL,
  `proxy` int(11) DEFAULT NULL,
  `referrer` int(11) NOT NULL,
  `custom_sequence` int(11) NOT NULL,
  `custom_sequence_block` int(11) NOT NULL,
  `witnesses_voted_for` int(11) NOT NULL,
  `witnesses_vote_weight` bigint(20) NOT NULL,
  `balance` bigint(20) NOT NULL,
  `shares` bigint(20) NOT NULL,
  `delegated` bigint(20) NOT NULL,
  `received` bigint(20) NOT NULL,
  `effective` bigint(20) NOT NULL,
  `to_withdraw` bigint(20) NOT NULL,
  `withdrawn` bigint(20) NOT NULL,
  `withdraw_rate` bigint(20) NOT NULL,
  `receiver_awards` bigint(20) NOT NULL,
  `benefactor_awards` bigint(20) NOT NULL,
  `energy` int(6) NOT NULL,
  `last_vote_time` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `activity` int(11) DEFAULT NULL,
  `update_time` int(11) NOT NULL,
  `update` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `update_time` (`update_time`),
  KEY `update` (`update`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `accounts_authority`;
CREATE TABLE `accounts_authority` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` int(11) NOT NULL,
  `type` int(1) NOT NULL,
  `agent` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `weight_threshold` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `accounts_keys`;
CREATE TABLE `accounts_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `key` varchar(53) NOT NULL,
  `weight` int(11) NOT NULL,
  `weight_threshold` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `accounts_snapshot`;
CREATE TABLE `accounts_snapshot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(10) NOT NULL,
  `time` int(11) NOT NULL,
  `account` int(11) NOT NULL,
  `balance` bigint(20) NOT NULL,
  `shares` bigint(20) NOT NULL,
  `delegated` bigint(20) NOT NULL,
  `received` bigint(20) NOT NULL,
  `effective` bigint(20) NOT NULL,
  `witnesses_voted_for` int(11) NOT NULL,
  `custom_sequence` int(11) NOT NULL,
  `initiator_ops_count` int(11) NOT NULL,
  `target_ops_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `blocks`;
CREATE TABLE `blocks` (
  `id` int(11) NOT NULL,
  `time` int(11) DEFAULT NULL,
  `hash` binary(20) DEFAULT NULL,
  `witness` int(11) DEFAULT NULL,
  `trx` int(11) DEFAULT NULL,
  `ops` int(11) DEFAULT NULL,
  `vops` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `chain_props_snapshot`;
CREATE TABLE `chain_props_snapshot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `current_shuffle_block` int(11) NOT NULL,
  `next_shuffle_block` int(11) NOT NULL,
  `scheduled_witnesses` tinyint(3) NOT NULL,
  `shuffled_witnesses` text NOT NULL,
  `majority_version` varchar(10) NOT NULL,
  `json` mediumtext NOT NULL,
  `account_creation_fee` int(11) NOT NULL,
  `maximum_block_size` int(11) NOT NULL,
  `create_account_delegation_ratio` int(11) NOT NULL,
  `create_account_delegation_time` int(11) NOT NULL,
  `min_delegation` int(11) NOT NULL,
  `min_curation_percent` int(5) NOT NULL,
  `max_curation_percent` int(5) NOT NULL,
  `bandwidth_reserve_percent` int(11) NOT NULL,
  `bandwidth_reserve_below` bigint(20) NOT NULL,
  `flag_energy_additional_cost` int(11) NOT NULL,
  `vote_accounting_min_rshares` int(11) NOT NULL,
  `committee_request_approve_min_percent` int(5) NOT NULL,
  `inflation_witness_percent` int(5) NOT NULL,
  `inflation_ratio_committee_vs_reward_fund` int(5) NOT NULL,
  `inflation_recalc_period` int(11) NOT NULL,
  `data_operations_cost_additional_bandwidth` int(11) NOT NULL,
  `witness_miss_penalty_percent` int(11) NOT NULL,
  `witness_miss_penalty_duration` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `delegations`;
CREATE TABLE `delegations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `shares` bigint(20) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `from` (`from`),
  KEY `to` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `dgp_snapshot`;
CREATE TABLE `dgp_snapshot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `json` longtext NOT NULL,
  `current_reserve_ratio` int(11) NOT NULL,
  `max_virtual_bandwidth` bigint(20) NOT NULL,
  `average_block_size` int(11) NOT NULL,
  `maximum_block_size` int(11) NOT NULL,
  `current_supply` bigint(20) NOT NULL,
  `committee_fund` bigint(20) NOT NULL,
  `committee_requests` int(11) NOT NULL,
  `total_vesting_fund` bigint(20) NOT NULL,
  `total_vesting_shares` bigint(20) NOT NULL,
  `total_reward_fund` bigint(20) NOT NULL,
  `total_reward_shares` bigint(20) NOT NULL,
  `last_irreversible_block_num` int(11) NOT NULL,
  `head_block_number` int(11) NOT NULL,
  `inflation_witness_percent` int(5) NOT NULL,
  `inflation_ratio` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `escrow`;
CREATE TABLE `escrow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `escrow_id` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `agent` int(11) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `fee` bigint(20) NOT NULL,
  `json` longtext NOT NULL,
  `time` int(11) NOT NULL,
  `ratification_deadline` int(11) NOT NULL,
  `expiration` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by_to` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by_agent` tinyint(1) NOT NULL DEFAULT 0,
  `dispute` tinyint(1) NOT NULL DEFAULT 0,
  `dispute_by` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `expired` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `active_escrow_id_from` (`active`,`escrow_id`,`from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ops`;
CREATE TABLE `ops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL,
  `block` int(11) DEFAULT NULL,
  `trx` int(11) DEFAULT NULL,
  `v` tinyint(1) NOT NULL DEFAULT 0,
  `json` longtext CHARACTER SET utf8mb4 DEFAULT NULL,
  `counted` tinyint(1) NOT NULL DEFAULT 0,
  `linked` tinyint(1) NOT NULL DEFAULT 0,
  `worked` tinyint(1) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL,
  `initiator` int(11) DEFAULT NULL,
  `target` int(11) DEFAULT NULL,
  `memo` text CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `counted_time` (`counted`,`time`),
  KEY `initiator` (`initiator`),
  KEY `target` (`target`),
  KEY `counted` (`counted`),
  KEY `counted_id` (`counted`,`id`),
  KEY `time_initiator_v` (`time`,`initiator`,`v`),
  KEY `time_target` (`time`,`target`),
  KEY `initiator_target_id` (`initiator`,`target`,`id`),
  KEY `linked` (`linked`),
  KEY `worked` (`worked`),
  KEY `block_trx` (`block`,`trx`),
  KEY `trx` (`trx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ops_link`;
CREATE TABLE `ops_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` int(11) NOT NULL,
  `op` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ops_type`;
CREATE TABLE `ops_type` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `d_count` int(11) NOT NULL,
  `w_count` int(11) NOT NULL,
  `m_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `stats`;
CREATE TABLE `stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `accounts_1` int(11) NOT NULL,
  `accounts_7` int(11) NOT NULL,
  `accounts_30` int(11) NOT NULL,
  `trx_count` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `trx`;
CREATE TABLE `trx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` binary(32) DEFAULT NULL,
  `block` int(11) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `ops` int(11) DEFAULT NULL,
  `vops` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash2` (`hash`),
  KEY `hash` (`hash`),
  KEY `block` (`block`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `witnesses`;
CREATE TABLE `witnesses` (
  `id` int(11) NOT NULL,
  `account` int(11) NOT NULL,
  `blocks` int(11) NOT NULL,
  `rewards` bigint(20) NOT NULL,
  `votes` bigint(20) NOT NULL,
  `penalty_percent` int(5) NOT NULL,
  `total_missed` int(5) NOT NULL,
  `url` varchar(255) NOT NULL,
  `props` longtext NOT NULL,
  `signing_key` varchar(53) NOT NULL,
  `running_version` varchar(10) NOT NULL,
  `hardfork_version_vote` varchar(10) NOT NULL,
  `created` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `update` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `account` (`account`),
  KEY `update_time` (`created`),
  KEY `update` (`update`),
  KEY `votes` (`votes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `witnesses_snapshot`;
CREATE TABLE `witnesses_snapshot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(10) NOT NULL,
  `time` int(11) NOT NULL,
  `witness` int(11) NOT NULL,
  `blocks` int(11) NOT NULL,
  `rewards` bigint(20) NOT NULL,
  `votes` bigint(20) NOT NULL,
  `penalty_percent` int(11) NOT NULL,
  `total_missed` int(11) NOT NULL,
  `votes_count` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `witness` (`witness`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `witnesses_votes`;
CREATE TABLE `witnesses_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` int(11) NOT NULL,
  `witness` int(11) NOT NULL,
  `votes` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`account`),
  KEY `witness` (`witness`),
  KEY `witness_votes` (`witness`,`votes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;