-- Migration: Add Google Maps link field to grounds table
-- Run this SQL to add the maps_link column

ALTER TABLE grounds ADD COLUMN maps_link VARCHAR(500) NULL AFTER image_path;

-- Add an index for better performance when searching by maps_link
CREATE INDEX idx_grounds_maps_link ON grounds(maps_link);
