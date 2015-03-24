
 select
        ivc1.name n1, ivc2.name n2,
        sum(case when ivc1.vote = ivc2.vote then 1 else 0 end) agree,
        sum(1) pairs,
        sum(case when ivc1.vote = ivc2.vote then 1 else 0 end)/sum(1) perc
      from meeting m
        join item i on i.meetingid = m.id
        join itemvote iv on iv.itemid = i.id
        join itemvotecast ivc1 on ivc1.itemvoteid = iv.id
        join itemvotecast ivc2 on ivc2.itemvoteid = iv.id
      where 
        m.starttime >= '2014-12-01'
        and ivc1.vote in ('y','n')
        and ivc2.vote in ('y','n')
        and ivc1.name not in ('L. A. (Sandy) Smallwood','B. Crew','B. Padolsky','F. Malo','G. Milner','C. Quinn','S. Burt')
        and ivc2.name not in ('L. A. (Sandy) Smallwood','B. Crew','B. Padolsky','F. Malo','G. Milner','C. Quinn','S. Burt')
      group by
        ivc1.name, ivc2.name
      order by
        ivc1.name, ivc2.name


