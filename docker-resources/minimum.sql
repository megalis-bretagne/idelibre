CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

--peut etre faire un minimum_test.sql pour eviter les collisions de nom

INSERT INTO role (id, name, composites)
VALUES ('94663bed-16e9-4990-8493-323c3d5e5565', 'SuperAdmin', '["ROLE_SUPERADMIN", "ROLE_MANAGE_STRUCTURES"]');

INSERT INTO role (id, name, composites)
VALUES ('306ed58f-7219-49ce-b269-7b7bce7ed3aa', 'GroupAdmin', '["ROLE_GROUP_ADMIN", "ROLE_MANAGE_STRUCTURES"]');

INSERT INTO role (id, name, composites)
VALUES ('160e7561-7ffa-459c-ae9b-4da32b49e1b1', 'Secretary', '["ROLE_SECRETARY"]');

INSERT INTO role (id, name, composites)
VALUES ('17f4b8ba-7a34-4463-9901-88b619a64be3', 'Admin', '["ROLE_STRUCTURE_ADMIN"]');

INSERT INTO "user" (id, structure_id, email, username, role_id, password, first_name, last_name)
values (UUID_GENERATE_V4(), NULL, 'superadmin@exemple.org', 'superadminprov', '94663bed-16e9-4990-8493-323c3d5e5565',
        '$argon2id$v=19$m=65536,t=4,p=1$jCNjXFnpctIdKy2XKJ3d9w$B2THO9hICaf20D73R6PB0FDiR1+2RpJCZlpG6RExTlg', 'super ',
        'admin ');


INSERT INTO timezone (id, name)
values (UUID_GENERATE_V4(), 'Europe/Paris');
INSERT INTO timezone (id, name)
values (UUID_GENERATE_V4(), 'Indian/Reunion');
INSERT INTO timezone (id, name)
values (UUID_GENERATE_V4(), 'America/Guadeloupe');
INSERT INTO timezone (id, name)
values (UUID_GENERATE_V4(), 'America/Martinique');
INSERT INTO timezone (id, name)
values (UUID_GENERATE_V4(), 'Pacific/Tahiti');
INSERT INTO timezone (id, name)
values (UUID_GENERATE_V4(), 'America/Cayenne');
INSERT INTO timezone (id, name)
values (UUID_GENERATE_V4(), 'Pacific/Noumea');
INSERT INTO timezone (id, name)
values (UUID_GENERATE_V4(), 'Indian/Mayotte');
