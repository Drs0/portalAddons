jQuery(document).ready(function ($) {
    function fetchMatches(leagueId) {
        $.post(fm_ajax.ajax_url, {
            action: 'fm_fetch_matches',
            league_id: leagueId
        }, function (response) {
            if (response.success && response.data.matches) {
                let matches = response.data.matches.slice(-5).reverse();
                let rows = '';
                matches.forEach(m => {
                    let home = m.homeTeam.name;
                    let away = m.awayTeam.name;
                    let score = `${m.score.fullTime.home ?? 0} - ${m.score.fullTime.away ?? 0}`;
                    rows += `<tr><td>${home}</td><td>${away}</td><td>${score}</td></tr>`;
                });
                $('#fm-matches tbody').html(rows);
            } else {
                $('#fm-matches tbody').html('<tr><td colspan="3">No matches found</td></tr>');
            }
        });
    }
    const leagueName = $('#fm-league-select');
    fetchMatches(leagueName.val());

    leagueName.on('change', function () {
        fetchMatches($(this).val());
    });
});
