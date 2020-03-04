
--
-- Create Root User
--

INSERT INTO `#__users` (`id`, `name`, `username`, `email`, `password`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES
    (960, 'Super User', '${adminUser}', '${adminEmail}', '${cryptPass}', 0, 1, '${installDate}', '0000-00-00 00:00:00', '', '');
INSERT INTO `#__user_usergroup_map` (`user_id`, `group_id`) VALUES (960, 8);
