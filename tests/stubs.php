<?php
// Brain Monkey stubs functions, not classes, so WP_Query needs a minimal stand-in.
// Tests configure the post count via the static $postCount before including the template.
if ( ! class_exists( 'WP_Query' ) ) {
	class WP_Query {
		public static int $postCount = 0;

		private int $remaining;

		public function __construct( array $args = [] ) {
			$this->remaining = self::$postCount;
		}

		public function have_posts(): bool {
			return $this->remaining-- > 0;
		}

		public function the_post(): void {
		}
	}
}
