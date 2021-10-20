CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

--peut etre faire un minimum_test.sql pour eviter les collisions de nom

INSERT INTO role (id, name, composites, is_in_structure_role, pretty_name)
VALUES ('94663bed-16e9-4990-8493-323c3d5e5565', 'SuperAdmin', '["ROLE_SUPERADMIN", "ROLE_MANAGE_STRUCTURES"]', false, 'Super administrateur');

INSERT INTO role (id, name, composites, is_in_structure_role, pretty_name)
VALUES ('306ed58f-7219-49ce-b269-7b7bce7ed3aa', 'GroupAdmin', '["ROLE_GROUP_ADMIN", "ROLE_MANAGE_STRUCTURES"]', false, 'Administrateur de groupe');

INSERT INTO role (id, name, composites, is_in_structure_role, pretty_name)
VALUES ('160e7561-7ffa-459c-ae9b-4da32b49e1b1', 'Secretary', '["ROLE_SECRETARY"]', true, 'Gestionnaire de séance');

INSERT INTO role (id, name, composites, is_in_structure_role, pretty_name)
VALUES ('230a1c1d-eaec-4fb7-9ba7-d7ac47dc97bb', 'Actor', '["ROLE_ACTOR"]', true, 'Elu');

INSERT INTO role (id, name, composites, is_in_structure_role, pretty_name)
VALUES ('17f4b8ba-7a34-4463-9901-88b619a64be3', 'Admin', '["ROLE_STRUCTURE_ADMIN"]', true, 'Administrateur');

INSERT INTO role (id, name, composites, is_in_structure_role, pretty_name)
VALUES ('e581587e-1694-4b83-9ed7-4994bc792e5b', 'Guest', '["ROLE_GUEST"]', true, 'Invité');

INSERT INTO role (id, name, composites, is_in_structure_role, pretty_name)
VALUES ('811a1329-0cd0-49e8-b59b-5394219c50dd', 'Employee', '["ROLE_EMPLOYEE"]', true, 'Personnel administratif');


INSERT INTO "user" (id, structure_id, email, username, role_id, password, first_name, last_name, is_active)
values (UUID_GENERATE_V4(), NULL, 'superadmin@exemple.org', 'superadminInstall', '94663bed-16e9-4990-8493-323c3d5e5565',
        '$argon2id$v=19$m=65536,t=4,p=1$jCNjXFnpctIdKy2XKJ3d9w$B2THO9hICaf20D73R6PB0FDiR1+2RpJCZlpG6RExTlg', 'super ',
        'admin ', true);


INSERT INTO timezone (id, name)
values ('069c8728-47b1-447a-a818-2fe4655d9bd2', 'Europe/Paris');
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

insert into api_role (id, name, composites, pretty_name)
  values ('ba98ff85-a720-4ca6-a877-c91de3ac4cf4', 'ApiStructureAdmin', '["ROLE_API_STRUCTURE_ADMIN"]', 'Administrateur api');


