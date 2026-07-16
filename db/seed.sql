-- NXTM seed data
-- Run after db/schema.sql. Two kinds of rows:
--   1. Color lookup tables (dataColorPalette, greenColor) -- these are pure
--      design/gradient data, required by the views' color-coding joins.
--      viewTasks/viewLists still work with zero rows here, they just render
--      without cc1/cc2/in2_color/in3_color styling -- but shipping them makes
--      a fresh install look like production.
--   2. A handful of generic placeholder categories/statuses so the Add Task
--      dropdowns (index.php, ptask.php) aren't empty on first run. Rename or
--      delete these from Admin once you're logged in -- nothing depends on
--      these specific rows by name.

-- ---------------------------------------------------------------------------
-- Color lookup tables
-- ---------------------------------------------------------------------------

INSERT INTO `dataColorPalette` (`number`, `colorcode`) VALUES
  (0,'f8696b'),(1,'BF1B00'),(2,'E85700'),(3,'D86F00'),(4,'E39F00'),
  (5,'EFC700'),(6,'D4E300'),(7,'A8E300'),(8,'73D600'),(9,'3FCF00'),(10,'00C700');

INSERT INTO `greenColor` (`ind`, `color_code`) VALUES
  (1,'ccffcc'),(2,'bff2bf'),(3,'b3e6b3'),(4,'a6d9a6'),(5,'99cc99'),
  (6,'8cbf8c'),(7,'80b280'),(8,'73a673'),(9,'669966'),(10,'598c59');

-- ---------------------------------------------------------------------------
-- Placeholder categories (Work Tasks / Personal Tasks "Category" dropdown)
-- ---------------------------------------------------------------------------

INSERT INTO `dataTaskCategories` (`name`, `color`, `fcolor`, `rowstat`) VALUES
  ('General', 'e9ecef', '212529', 0),
  ('Urgent',  'dc3545', 'ffffff', 0),
  ('Waiting', 'ffc107', '212529', 0);

-- ---------------------------------------------------------------------------
-- Placeholder statuses (Work Tasks / Personal Tasks "Status" dropdown)
-- ---------------------------------------------------------------------------

INSERT INTO `datastatus` (`status`, `color`, `fcolor`, `rowstat`) VALUES
  ('1 Now',    'b10202', 'ffffff', 0),
  ('2 Active', '11734b', 'd4edbc', 0),
  ('3 Parking Lot', 'e8e8ea', '000000', 0),
  ('4 Done',   'e8e8e8', '000000', 0);
