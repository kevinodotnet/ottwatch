
drop table t; 

create table t as 
	select max(id) id 
	from md5hist 
	group by curmd5,prevmd5 
	having count(1) > 1; 

delete from md5hist where id in ( select id from t );

