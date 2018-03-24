update userphasesteps set creationdt = '2016-09-18 00:00:00'
where creationdt = '0000-00-00 00:00:00'

insert into userplanphases (planphaseid, userid, starteddate, completiondate, creationdt, lastupdated)
select ps.planphaseid, ups.userid, min(creationdt), (
    select min(creationdt) from userphasesteps ups2 inner JOIN 
    	phasesteps ps2 on ps2.id = ups2.phasestepid inner join
		planphases pp2 on pp2.id = ps2.planphaseid 
    where ups2.userid = ups.userid and pp2.number = pp.number + 1 
    group by ps2.planphaseid, ups2.userid
), UTC_DATE(), UTC_DATE()  from 
userphasesteps ups inner JOIN
phasesteps ps on ps.id = ups.phasestepid inner join
planphases pp on pp.id = ps.planphaseid
group by ps.planphaseid, ups.userid


