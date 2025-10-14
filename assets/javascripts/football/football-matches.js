document.addEventListener('DOMContentLoaded', function () {
  const leagueSelect = document.getElementById('fm-league-select');
  if (!leagueSelect) return;

  fetchMatches(leagueSelect.value);

  leagueSelect.addEventListener('change', function () {
    fetchMatches(this.value);
  });
});

function fetchMatches(leagueId) {
  const formData = new FormData();
  formData.append('action', 'fm_fetch_matches');
  formData.append('league_id', leagueId);

  fetch(fm_ajax.ajax_url, {
    method: 'POST',
    body: formData,
  })
    .then(response => response.json())
    .then(response => {
      const tableBody = document.querySelector('#fm-matches tbody');

      if (response.success && response.data.matches) {
        const matches = response.data.matches.slice(-5).reverse();
        let rows = '';
        matches.forEach(m => {
          const home = m.homeTeam.name;
          const away = m.awayTeam.name;
          const score = `${m.score.fullTime.home ?? 0} - ${m.score.fullTime.away ?? 0}`;
          rows += `<tr><td>${home}</td><td>${away}</td><td>${score}</td></tr>`;
        });
        tableBody.innerHTML = rows;
      } else {
        tableBody.innerHTML = '<tr><td colspan="3">No matches found</td></tr>';
      }
    })
    .catch(error => {
      console.error('Error fetching matches:', error);
      const tableBody = document.querySelector('#fm-matches tbody');
      if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="3">Error loading matches</td></tr>';
      }
    });
}
