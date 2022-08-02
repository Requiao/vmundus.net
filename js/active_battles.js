$(document).ready( function() {
	//update battle clock.
	updateBattleClock();
	function updateBattleClock() {
		$('.battle_duration').each(function() {
			countupClock(this);
		});
		var t = setTimeout(updateBattleClock, 950);
	};
});