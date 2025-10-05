<div id="fm-container" class="fm-container <?php echo apply_filters('customFmClass','');?>">
    <select id="fm-league-select" class="fm-league-select <?php echo apply_filters('customFmLeagueClass','');?>">
        <option value="PL">Premier League</option>
        <option value="PD">La Liga</option>
        <option value="SA">Serie A</option>
        <option value="BL1">Bundesliga</option>
        <option value="FL1">Ligue 1</option>
        <option value="CL">Champions League</option>
    </select>
    <table id="fm-matches" class="fm-matches <?php echo apply_filters('customFmMatchesClass','');?>">
        <thead>
            <tr>
                <th>Home</th>
                <th>Away</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
