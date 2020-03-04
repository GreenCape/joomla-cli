
--
-- Create Root User
--

INSERT INTO "#__users" ("id", "name", "username", "email", "password", "usertype", "block", "sendEmail", "gid", "registerDate", "lastvisitDate", "activation", "params") VALUES
    (62, 'Administrator', '${adminUser}', '${adminEmail}', '${cryptPass}', 'Super Administrator', 0, 1, 25, '${installDate}', '1970-01-01 00:00:00', '', '');
INSERT INTO "#__core_acl_aro" ("aro_id", "section_value", "value", "order_value", "name", "hidden") VALUES (10, 'users', '62', 0, 'Administrator', 0);
INSERT INTO "#__core_acl_groups_aro_map" ("group_id", "section_value", "aro_id") VALUES (25, '', 10);
