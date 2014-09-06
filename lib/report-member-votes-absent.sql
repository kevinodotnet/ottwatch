
  select
    m.category,
    ic.name,
    sum(case when vote = 'y' then 1 else 0 end) vote_y,
    sum(case when vote = 'n' then 1 else 0 end) vote_n,
    sum(case when vote = 'a' then 1 else 0 end) vote_a,
    count(1) votes,
    sum(case when vote = 'y' then 1 else 0 end)/count(1) perc_y,
    sum(case when vote = 'n' then 1 else 0 end)/count(1) perc_n,
    sum(case when vote = 'a' then 1 else 0 end)/count(1) perc_a
  from
    meeting m
    join item i on i.meetingid = m.id
    join itemvote iv on iv.itemid = i.id
    join itemvotecast ic on ic.itemvoteid = iv.id
  group by
    m.category,
    ic.name
  order by
    m.meetid, vote_a desc, ic.name
  ;

