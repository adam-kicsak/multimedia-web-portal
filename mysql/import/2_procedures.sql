
delimiter $$

drop procedure if exists `UserCreate` $$
create procedure `UserCreate` (`_id` varchar(30), `_email` varchar(255), `_secret` varchar(50))
begin
    insert into `user` (`id`, `email`, `secret`) values (`_id`, `_email`, sha1(`_secret`));
end $$

drop procedure if exists `UserPasswordChange` $$
create procedure `UserPasswordChange` (in `_id` varchar(30), in `_secret` varchar(50)) 
begin
	update `user` set `secret` = sha1(`_secret`) where `id` = `_id`;
end $$

drop procedure if exists `UserPromoteToModerator` $$
create procedure `UserPromoteToModerator` (in `_id` varchar(30)) 
begin
	update `user` set `role` = 'moderator' where `id` = `_id`;
end $$

drop function if exists `UserAuthenticate` $$
create function `UserAuthenticate`(`_email` varchar(255), `_secret` varchar(50)) returns varchar(30)
begin
	declare `res` varchar(30) default null;
	select `id` into `res` from `user` where `secret` = sha1(`_secret`) and `email` = `_email`;
	return `res`;
end $$

drop procedure if exists `UserSelectById` $$
create procedure `UserSelectById` (`_id` varchar(30))
begin
	select * from `user` where `id` = `_id`;
end $$



drop function if exists `UserNoteCreate` $$
create function `UserNoteCreate`(`_user_id` varchar(30), `_author_id` varchar(30), `_type` enum('note', 'warn', 'ban'),
	`_reason` text, `_expire` timestamp) returns int(11) unsigned
begin
	insert into `user_note` (`user_id`, `author_id`, `type`, `reason`, `expire`)
		values(`_user_id`, `_author_id`, `_type`, `_reason`,`_expire`);
	return last_insert_id();
end $$


drop procedure if exists `UserNoteSelectById` $$
create procedure `UserNoteSelectById`(in `_id` int(11) unsigned)
begin
	select * from `user_note` where `id` = `_id`;
end $$

drop procedure if exists `UserNoteSelectByUser` $$
create procedure `UserNoteSelectByUser`(in `_user_id` varchar(30))
begin
	select * from `user_note` where `user_id` = `_user_id`;
end $$

drop procedure if exists `UserNoteNotExpiredSelectByUser` $$
create procedure `UserNoteNotExpiredSelectByUser`(in `_user_id` varchar(30))
begin
	select * from `user_note` where `user_id` = `_user_id` and `expire` > current_timestamp();
end $$



drop function if exists `MediaCreate` $$
create function `MediaCreate`(`_type` enum('audio','video','image'), `_title` varchar(100), `_description` text, 
	`_user_id` varchar(30), `_searchable` tinyint(1)) returns int(11) unsigned
begin
	insert into `media` (`type`, `title`, `description`, `user_id`, `searchable`)
		values (`_type`, `_title`, `_description`, `_user_id`, `_searchable`);
	return last_insert_id();
end $$

drop procedure if exists `MediaRemove` $$
create procedure `MediaRemove`(in `_id` int(11) unsigned, `_by` enum('author', 'moderator'))
begin
	update `media` set `removed` = `_by` where `id` = `_id`;
end $$

drop procedure if exists `MediaSelectById` $$
create procedure `MediaSelectById`(in `_id` int(11) unsigned)
begin
	select * from `media` where `id` = `_id`;
end $$

drop procedure if exists `MediaSelectByUser` $$
create procedure `MediaSelectByUser`(in `_user_id` varchar(30), in `_paginate` tinyint(3), in `page` smallint(4))
begin
	declare `first_record` int(10);
	if (`_paginate` is not null) and (`page` is not null) then
		set @skip = `page` * `_paginate`;
		set @rows = `_paginate`;
		set @user_id = `_user_id`;
		prepare `stmt` from 'select sql_calc_found_rows * from `media` where `user_id` = ? and removed = "no" order by `created` desc limit ?, ?';
		execute `stmt` using @user_id, @skip, @rows;
	else
		select * from `media` where `user_id` = `_user_id` and removed = "no" order by `created` desc;
	end if;
end $$

drop procedure if exists `MediaSelectByAlbum` $$
create procedure `MediaSelectByAlbum`(in `_album_id` int(11) unsigned, in `_paginate` tinyint(3), in `page` smallint(4))
begin
	if `_paginate` is not null and `page` is not null then
		set @skip = `page` * `_paginate`;
		set @rows = `_paginate`;
		set @album_id = `_album_id`;
		prepare `stmt` from 
			'select sql_calc_found_rows `media`.* from `media` inner join `album_content` on `media`.`id` = `album_content`.`media_id`
			where `album_content`.`album_id` = ? order by `album_content`.`order` limit ? , ?';
		execute `stmt` using @album_id, @skip, @rows;
	else
		select `media`.* from `media` inner join `album_content` on `media`.`id` = `album_content`.`media_id`
			where `album_content`.`album_id` = `_album_id` order by `album_content`.`order`;
	end if;
end $$

drop procedure if exists `MediaAndAlbumSelectByAlbumAndOrder` $$
create procedure `MediaAndAlbumSelectByAlbumAndOrder`(in `_album_id` int(11) unsigned, in `_order` int(11))
begin
        select `media`.*, `album_content`.* from `media` inner join `album_content` on `media`.`id` = `album_content`.`media_id`
                where `album_content`.`album_id` = `_album_id` and `album_content`.`order` between `_order` - 3 and `_order` + 3 order by `album_content`.`order`;
end $$


drop procedure if exists `MediaSearch` $$
create procedure `MediaSearch` (in `_pattern` varchar(100), in `_type` enum('image', 'audio', 'video'), in `_timeusing` enum('elder', 'younger', 'unused'), in `_time` enum('1d', '3d', '1w', '2w', '1m', '3m', '6m', '1y'), in `_paginate` tinyint(3), in `page` smallint(4))
begin
	if `_type` is null then
		set @pattern = `_pattern`;
		set @skip = `page` * `_paginate`;
		set @rows = `_paginate`;
		prepare `stmt` from 
			'select sql_calc_found_rows * from `media` where `searchable` = 1 and removed = "no" and (`title` rlike ? or `description` rlike ?)
	order by `created` desc limit ?, ?';
		execute `stmt` using @pattern, @pattern, @skip, @rows;
	else
		if `_timeusing` <> 'unused' then
			case `_time`
				when '1d' then set @date = date_sub(curdate(), interval 1 day);
				when '3d' then set @date = date_sub(curdate(), interval 3 day);
				when '1w' then set @date = date_sub(curdate(), interval 1 week);
				when '2w' then set @date = date_sub(curdate(), interval 2 week);
				when '1m' then set @date = date_sub(curdate(), interval 1 month);
				when '3m' then set @date = date_sub(curdate(), interval 3 month);
				when '6m' then set @date = date_sub(curdate(), interval 6 month);
				when '1y' then set @date = date_sub(curdate(), interval 1 year);
			end case;
		end if;
		set @pattern = `_pattern`;
		set @type = `_type`;
		set @skip = `page` * `_paginate`;
		set @rows = `_paginate`;
		case `_timeusing`
			when 'unused' then
				prepare `stmt` from 
					'select sql_calc_found_rows * from `media` where `type` = ? and `searchable` = 1 and removed = "no" and (`title` rlike ? or `description` rlike ?)
			order by `created` desc limit ?, ?';
				execute `stmt` using @type, @pattern, @pattern, @skip, @rows;
			when 'younger' then
				prepare `stmt` from
					'select sql_calc_found_rows * from `media` where `type` = ? and `searchable` = 1 and removed = "no" and `created` > ? and (`title` rlike ? or `description` rlike ?)
			order by `created` desc limit ?, ?';
				execute `stmt` using @type, @date, @pattern, @pattern, @skip, @rows;
			when 'elder' then
				prepare `stmt` from
					'select sql_calc_found_rows * from `media` where `type` = ? and `searchable` = 1 and removed = "no" and `created` < ? and (`title` rlike ? or `description` rlike ?)
			order by `created` desc limit ?, ?';
				execute `stmt` using @type, @date, @pattern, @pattern, @skip, @rows;
		end case;
	end if;
end $$



drop function if exists `MediaCommentAdd` $$
create function `MediaCommentAdd`(`_user_id` varchar(30), `_media_id` int(11) unsigned, `_comment` text) returns int(11) unsigned
begin
	insert into `media_comment` (`user_id`, `media_id`, `comment`) values(`_user_id`, `_media_id`, `_comment`);
	return last_insert_id();
end $$

drop procedure if exists `MediaCommentRemove` $$
create procedure `MediaCommentRemove`(in `_id` int(11) unsigned, `_by` enum('author', 'moderator'))
begin
	update `media_comment` set `removed` = `_by` where `id` = `_id`;
end $$

drop procedure if exists `MediaCommentSelectById` $$
create procedure `MediaCommentSelectById` (in `_id` int(11) unsigned)
begin
		select * from `media_comment` where `id` = `_id`;
end $$

drop procedure if exists `MediaCommentSelectByMedia` $$
create procedure `MediaCommentSelectByMedia` (in `_media_id` int(11) unsigned, in `_paginate` tinyint(3), in `page` smallint(4))
begin
	if `_paginate` is not null and `page` is not null then
		set @skip = `page` * `_paginate`;
		set @rows = `_paginate`;
		set @media_id = `_media_id`;
		prepare `stmt` from 'select sql_calc_found_rows * from `media_comment` where `media_id` = ? order by `created` desc limit ?, ?';
		execute `stmt` using @media_id, @skip, @rows;
	else
		select * from `media_comment` where `media_id` = `_media_id` order by `created` desc;
	end if;
end $$



drop procedure if exists `FavoriteAdd` $$
create procedure `FavoriteAdd`(in `_user_id` varchar(30), `_media_id` int(11) unsigned)
begin
	insert into `favorite` (`user_id`, `media_id`) values(`_user_id`, `_media_id`);
end $$

drop procedure if exists `FavoriteRemove` $$
create procedure `FavoriteRemove`(in `_user_id` varchar(30), `_media_id` int(11) unsigned)
begin
	delete from `favorite` where `user_id` = `_user_id` and `media_id` = `_media_id`;
end $$

drop procedure  if exists `FavoriteSelectByUser` $$
create procedure `FavoriteSelectByUser`(in `_user_id` varchar(30), in `_paginate` tinyint(3), in `page` smallint(4))
begin
	if `_paginate` is not null and `page` is not null then
		set @skip = `page` * `_paginate`;
		set @rows = `_paginate`;
		set @user_id = `_user_id`;
		prepare `stmt` from 'select sql_calc_found_rows `media`.* from `media` inner join `favorite` on `media`.`id` = `favorite`.`media_id`
			where `favorite`.`user_id` = ? order by `favorite`.`adding` limit ?, ?';
		execute `stmt` using @user_id, @skip, @rows;
	else
		select `media`.* from `media` inner join `favorite` on `media`.`id` = `favorite`.`media_id`
			where `favorite`.`user_id` = `_user_id` order by `favorite`.`adding`;
	end if;
end $$



drop function if exists `AlbumCreate` $$
create function `AlbumCreate`(`_user_id` varchar(30), `_title` varchar(100), `_description` text) returns int(11) unsigned
begin
	insert into `album` (`user_id`, `title`, `description`)
		values(`_user_id`, `_title`, `_description`);
	return last_insert_id();
end $$

drop procedure if exists `AlbumDelete` $$
create procedure `AlbumDelete`(in `_id` int(11) unsigned)
begin
	delete from `album` where `id` = `_id`;
end $$

drop procedure if exists `AlbumSelectById` $$
create procedure `AlbumSelectById` (in `_id` int(11) unsigned) 
begin
	select * from `album` where `id` = `_id`;
end $$

drop procedure if exists `AlbumSelectByUser` $$
create procedure `AlbumSelectByUser` (in `_user_id` varchar(30), in `_paginate` tinyint(3), in `page` smallint(4))
begin
	if `_paginate` is not null and `page` is not null then
		set @skip = `page` * `_paginate`;
		set @rows = `_paginate`;
		set @user_id = `_user_id`;
		prepare `stmt` from 'select sql_calc_found_rows  * from `album` where `user_id` = ? order by `created` desc limit ?, ?';
		execute `stmt` using @user_id, @skip, @rows;
	else
		select * from `album` where `user_id` = `_user_id` order by `created` desc;
	end if;
end $$


drop procedure if exists `AlbumSearch` $$
create procedure `AlbumSearch` (in `_pattern` varchar(100), in `_timeusing` enum('elder', 'younger', 'unused'), in `_time` enum('1d', '3d', '1w', '2w', '1m', '3m', '6m', '1y'), in `_paginate` tinyint(3), in `page` smallint(4))
begin
    if `_timeusing` <> 'unused' then
        case `_time`
            when '1d' then set @date = date_sub(curdate(), interval 1 day);
            when '3d' then set @date = date_sub(curdate(), interval 3 day);
            when '1w' then set @date = date_sub(curdate(), interval 1 week);
            when '2w' then set @date = date_sub(curdate(), interval 2 week);
            when '1m' then set @date = date_sub(curdate(), interval 1 month);
            when '3m' then set @date = date_sub(curdate(), interval 3 month);
            when '6m' then set @date = date_sub(curdate(), interval 6 month);
            when '1y' then set @date = date_sub(curdate(), interval 1 year);
        end case;
    end if;
    set @pattern = `_pattern`;
    set @skip = `page` * `_paginate`;
    set @rows = `_paginate`;
    case `_timeusing`
        when 'unused' then
            prepare `stmt` from
                'select sql_calc_found_rows * from `album` where (`title` rlike ? or `description` rlike ?)
		order by `created` desc limit ?, ?';
            execute `stmt` using @pattern, @pattern, @skip, @rows;
        when 'younger' then
            prepare `stmt` from
                'select sql_calc_found_rows * from `album` where `created` > ? and (`title` rlike ? or `description` rlike ?)
		order by `created` desc limit ?, ?';
            execute `stmt` using @date, @pattern, @pattern, @skip, @rows;
        when 'elder' then
            prepare `stmt` from
                'select sql_calc_found_rows * from `album` where a`created` < ? and (`title` rlike ? or `description` rlike ?)
		order by `created` desc limit ?, ?';
            execute `stmt` using @date, @pattern, @pattern, @skip, @rows;
    end case;
end $$


drop procedure if exists `AlbumCotentAdd` $$
create procedure `AlbumCotentAdd`(`_album_id` int(11) unsigned, `_media_id` int(11) unsigned, in `_number` int(11) unsigned)
begin
	declare `new_order` tinyint(3) default null;
	select max(`order`) + 1 into `new_order` from `album_content` where `album_id` = `_album_id`;
	if `new_order` is null then
			set `new_order` = 1;
	else
		if `_number` is not null and `_number` > 0 and `_number` < `new_order` then
			set `new_order` = `_number`;
		end if;
	end if;
	update `album_content` set `order` = `order` + 1 where `album_id` = `_album_id` and `order` >= `new_order`;
	insert into `album_content` (`album_id`, `media_id`, `order`) values(`_album_id`, `_media_id`, `new_order`);
end $$

drop procedure if exists `AlbumContentRemove` $$
create procedure `AlbumContentRemove`(in `_id` int(11) unsigned)
begin
	declare `old_order` smallint(5) default 32767;
	declare `old_album_id` bigint(20) default null;
	select `album_id`, `order` into `old_album_id`, `old_order` from `album_content` where `id` = `_id`;
	if `old_album_id` is not null then
		delete from `album_content` where `id` = `_id`;
		update `album_content` set `order` = `order` -1  where `abum_id` = `old_album_id` and `order` < `old_order`;
	end if;
end $$


drop procedure if exists `ConvertStateCreate` $$
create procedure `ConvertStateCreate`(`_user_id` varchar(30), `_file` varchar(255), `_type` enum('audio','video','image'))
begin
    insert into `convert_state` (`user_id`, `file`, `type`) values (`_user_id`, `_file`, `_type`);
end $$

drop procedure if exists `ConvertStateDelete` $$
create procedure `ConvertStateDelete`(`_user_id` varchar(30))
begin
    delete from `convert_state` where `user_id` = `_user_id`;
end $$

drop procedure if exists `ConvertStateModify` $$
create procedure `ConvertStateModify`(`_user_id` varchar(30), `_phase` tinyint(3) unsigned)
begin
    update `convert_state` set `phase` = `_phase` where `user_id` = `_user_id`;
end $$

drop procedure if exists `ConvertStateSelectByUser` $$
create procedure `ConvertStateSelectByUser`(`_user_id` varchar(30))
begin
    select * from `convert_state` where `user_id` = `_user_id`;
end $$

delimiter ;