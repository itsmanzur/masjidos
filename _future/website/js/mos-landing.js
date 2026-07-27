/**
 * MasjidOS landing helpers (demo tabs).
 * Optional: enqueue on assembled pages, or paste into an Elementor HTML widget once.
 */
(function () {
	function initDemo(root) {
		var tabs = root.querySelectorAll(".mos-demo__tab");
		var panels = root.querySelectorAll(".mos-demo__panel");
		if (!tabs.length) return;

		tabs.forEach(function (tab) {
			tab.addEventListener("click", function () {
				var id = tab.getAttribute("data-demo");
				tabs.forEach(function (t) {
					t.classList.toggle("is-active", t === tab);
				});
				panels.forEach(function (p) {
					p.classList.toggle("is-active", p.getAttribute("data-demo") === id);
				});
			});
		});
	}

	document.querySelectorAll(".mos-demo").forEach(initDemo);
})();
