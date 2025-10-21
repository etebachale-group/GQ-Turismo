ALTER TABLE guias_turisticos
ADD COLUMN current_latitude DECIMAL(10, 8) NULL,
ADD COLUMN current_longitude DECIMAL(11, 8) NULL,
ADD COLUMN last_updated DATETIME NULL;