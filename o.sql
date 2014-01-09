
select
	lower(f.client) client,
	case when l.electedofficialid is not null then l.lobbiednorm else 'staff' end lobbied
from
	lobbying l
	join lobbyfile f on f.id = l.lobbyfileid
where
	lower(f.client) != 'city of ottawa'
	and lower(f.client) != 'city ottawa'
	and lower(f.client) != 'ottawa'

