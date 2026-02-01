INSERT INTO locations (name) VALUES ('Attic - West Wall');
INSERT INTO locations (name) VALUES ('Attic - East Wall');
INSERT INTO locations (name) VALUES ('Shed - Shelf A');

INSERT INTO boxes (qr_code, location_id, notes) VALUES ('BOX-TEST01', 1, 'Holiday Decorations');
INSERT INTO items (box_id, name, description) VALUES (1, 'Xmas Lights', 'Red and Green LED string lights');
INSERT INTO items (box_id, name, description) VALUES (1, 'Ornaments', 'Box of glass balls');
