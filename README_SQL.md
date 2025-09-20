-- SQL migrations for inventory & reporting enhancements

-- 1) Add stock and unit columns to Fertilizer/Pesticide if missing
ALTER TABLE Fertilizer ADD COLUMN IF NOT EXISTS StockQuantity DECIMAL(12,2) NOT NULL DEFAULT 0;
ALTER TABLE Fertilizer ADD COLUMN IF NOT EXISTS Unit VARCHAR(10) NULL;

ALTER TABLE Pesticide ADD COLUMN IF NOT EXISTS StockQuantity DECIMAL(12,2) NOT NULL DEFAULT 0;
ALTER TABLE Pesticide ADD COLUMN IF NOT EXISTS Unit VARCHAR(10) NULL;

-- 1b) Optional pricing columns
ALTER TABLE Fertilizer ADD COLUMN IF NOT EXISTS PurchasePrice DECIMAL(12,2) NULL;
ALTER TABLE Fertilizer ADD COLUMN IF NOT EXISTS SalePrice DECIMAL(12,2) NULL;
ALTER TABLE Pesticide ADD COLUMN IF NOT EXISTS PurchasePrice DECIMAL(12,2) NULL;
ALTER TABLE Pesticide ADD COLUMN IF NOT EXISTS SalePrice DECIMAL(12,2) NULL;

-- 2) Ensure DailyReport table has necessary columns
-- Base table example (create if not exists) - adjust engine/charset as needed
CREATE TABLE IF NOT EXISTS DailyReport (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_type ENUM('fertilizer','pesticide') NOT NULL,
  item_name VARCHAR(255) NOT NULL,
  customer_name VARCHAR(255) NULL,
  quantity DECIMAL(12,2) NOT NULL,
  total_sales DECIMAL(12,2) NOT NULL DEFAULT 0,
  unit VARCHAR(10) NOT NULL,
  report_date DATE NOT NULL,
  order_date DATE NOT NULL
) ENGINE=InnoDB;

-- 3) Add optional item_id column to DailyReport for referential link
ALTER TABLE DailyReport ADD COLUMN IF NOT EXISTS item_id INT NULL;

-- Optional: create indexes for faster filtering
CREATE INDEX IF NOT EXISTS idx_daily_report_date ON DailyReport (report_date);
CREATE INDEX IF NOT EXISTS idx_daily_item_type ON DailyReport (item_type);

-- 4) Payment-related columns
ALTER TABLE DailyReport ADD COLUMN IF NOT EXISTS paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0;
ALTER TABLE DailyReport ADD COLUMN IF NOT EXISTS payment_status ENUM('paid','partial','unpaid') NULL;

