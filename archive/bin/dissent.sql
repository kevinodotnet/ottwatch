
/*
	create or replace view itemvotecast_summary1 as
		select 
			itemvoteid,
			sum(case 
				when vote = 'y' then 1
				else 0 end
			) y_votes,
			sum(case 
				when vote = 'n' then 1
				else 0 end
			) n_votes,
			sum(case 
				when vote = 'y' then 1
				when vote = 'n' then 1
				else 0 end
			) yn_votes
		from itemvotecast
		group by itemvoteid
		;
		*/

/*
	create or replace view itemvotecast_summary2 as
	select 
		ivc1.itemvoteid,
		vote,
		count(1) asVote, 
		c.yn_votes,
		case when count(1)/c.yn_votes >= 0.50 then 1 else 0 end majority
	from itemvotecast ivc1
		join itemvotecast_summary1 c on c.itemvoteid = ivc1.itemvoteid
	group by itemvoteid,vote
	;

	select * from itemvotecast_summary2 
	where itemvoteid = 48426;
*/

  select 
		ivc1.itemvoteid,
		ivc1.vote,
		ivc1.name name1,
		ivc2.name name2,
		s.majority,
		m.category,
		m.starttime
	from itemvotecast ivc1
		join itemvotecast ivc2 on 
			ivc1.itemvoteid = ivc2.itemvoteid
			and ivc1.vote = ivc2.vote
		join itemvotecast_summary2 s on 
			s.itemvoteid = ivc1.itemvoteid
			and s.vote = ivc1.vote
		join itemvote iv on iv.id = ivc1.itemvoteid
		join item i on i.id = iv.itemid
		join meeting m on m.id = i.meetingid
	where 
		m.starttime > '2014-09-09'
		and ivc1.vote in ('y','n')
		and ivc2.vote in ('y','n')
		and s.majority = 0

