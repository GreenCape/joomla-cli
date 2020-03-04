
--
-- Create Root User
--

INSERT INTO `#__users` (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES
    (42, 'Super User', '${adminUser}', '${adminEmail}', '${cryptPass}', 'deprecated', 0, 1, '${installDate}', '0000-00-00 00:00:00', '', '');
INSERT INTO `#__user_usergroup_map` (`user_id`, `group_id`) VALUES (42, 8);
