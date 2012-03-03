ALTER TABLE  `raid_User` ADD  `Created` DATETIME NOT NULL;
INSERT INTO `raid_Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidStartHour', '19', ''), (NULL, 'RaidStartMinute', '30', '');
INSERT INTO `raid_Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidEndHour', '23', ''), (NULL, 'RaidEndMinute', '0', '');
INSERT INTO `raid_Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'RaidSize', '10', '');
INSERT INTO `raid_Setting` (`SettingId`, `Name`, `IntValue`, `TextValue`) VALUES (NULL, 'Site', '', ''), (NULL, 'Banner', '', 'cata');