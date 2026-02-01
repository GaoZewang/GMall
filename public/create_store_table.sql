-- 创建店铺表
CREATE TABLE `gm_admin_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '门店ID',
  `merchant_id` int(11) NOT NULL COMMENT '商户ID，关联商户表',
  `admin_user_id` int(11) NOT NULL COMMENT '人员id',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `revenue` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '营业额',
  `name` varchar(255) NOT NULL COMMENT '门店名称',
  `address` text COMMENT '门店地址',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '门店联系电话',
  `status` tinyint(1) DEFAULT '1' COMMENT '门店状态，1：启用，0：禁用',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_merchant_id` (`merchant_id`),
  CONSTRAINT `fk_merchant_id` FOREIGN KEY (`merchant_id`) REFERENCES `gm_admin_merchant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='门店表';