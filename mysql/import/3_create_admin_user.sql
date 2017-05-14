call `UserCreate`('admin', 'admin@localhost', 'changeit');
update `user` set `role` = 'administrator' where `id` = 'admin';
commit;
