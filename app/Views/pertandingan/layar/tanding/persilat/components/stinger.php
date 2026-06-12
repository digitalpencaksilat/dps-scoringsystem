<style>
	#stinger-container {
		position: fixed;
		top: 0;
		left: 0;
		width: 100vw;
		height: 101vh;
		z-index: 9999;
		pointer-events: none;
		overflow: hidden;
		display: none;
	}

	.stinger-slash {
		position: absolute;
		width: 100%;
		height: 120%;
		top: -10%;
		background: #1a1a1a;
		transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1);
		will-change: transform;
	}

	#stinger-left {
		left: -100%;
		transform: skewX(-20deg) translateX(0);
		z-index: 1;
		box-shadow: 15px 0 50px rgba(0,0,0,0.7);
		background: linear-gradient(90deg, #0f0f0f, #222);
	}

	#stinger-right {
		right: -100%;
		transform: skewX(-20deg) translateX(0);
		z-index: 2;
		box-shadow: -15px 0 50px rgba(0,0,0,0.7);
		background: linear-gradient(270deg, #0f0f0f, #222);
	}

	#stinger-text {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
		color: #ffffff;
		font-weight: 900;
		text-align: center;
		z-index: 10;
		opacity: 0;
		transform: scale(0.6);
		transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
		font-size: 13em;
		text-shadow: 0 10px 40px rgba(0,0,0,0.9);
	}

	#stinger-container.active {
		display: block;
	}

	#stinger-container.animate-in #stinger-left {
		transform: skewX(-20deg) translateX(65%);
	}

	#stinger-container.animate-in #stinger-right {
		transform: skewX(-20deg) translateX(-65%);
	}

	#stinger-container.animate-in #stinger-text {
		opacity: 1;
		transform: scale(1);
	}
</style>

<div id="stinger-container">
	<div id="stinger-left" class="stinger-slash"></div>
	<div id="stinger-right" class="stinger-slash"></div>
	<p id="stinger-text"></p>
</div>

<script>
	const stinger = {
		set_text: function($text) {
			$('#stinger-text').html($text);
		},
		set_font_size: function($font_size) {
			$('#stinger-text').css('font-size', $font_size);
		},
		start_animation: function(callback = null) {
			const $container = $('#stinger-container');
			$container.show().addClass('active');

			requestAnimationFrame(() => {
				$container.addClass('animate-in');
			});

			if (callback !== null) {
				setTimeout(callback, 800);
			}
		},
		end_animation: function(callback = null) {
			const $container = $('#stinger-container');
			$container.removeClass('animate-in');

			setTimeout(() => {
				$container.hide().removeClass('active');
				if (callback !== null) {
					callback();
				}
			}, 700);
		}
	};
</script>
