select 
	concat(c.last, ', ', c.first) candidate,
	w.ward_en ward,
	d.id, d.name, d.address, d.city, d.prov, d.postal, d.amount, d.type
from candidate_donation d 
	join candidate_return r on r.id = d.returnid 
	join candidate c on c.id = r.candidateid 
	join wards_2010 w on ST_Contains(w.shape,d.location) 
where 
	c.ward = 0 
	and w.ward_en in ('KITCHISSIPPI')
	and d.type = 0
order by
	instr(d.name,','), d.id
