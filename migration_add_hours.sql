-- Migration: Add hours_per_slot field to time_slots table
-- Run this if you have an existing database

USE box_cricket;

-- Add hours_per_slot column if it doesn't exist
ALTER TABLE time_slots ADD COLUMN IF NOT EXISTS hours_per_slot DECIMAL(3,1) NOT NULL DEFAULT 1.0;

-- Update existing slots to have 1 hour by default
UPDATE time_slots SET hours_per_slot = 1.0 WHERE hours_per_slot IS NULL OR hours_per_slot = 0;

-- Update existing bookings to recalculate total_amount based on slot hours
UPDATE bookings b 
JOIN time_slots t ON b.slot_id = t.id 
JOIN grounds g ON b.ground_id = g.id 
SET b.total_amount = g.price_per_hour * t.hours_per_slot;
