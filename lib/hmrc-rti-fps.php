<?php

	class hmrc_rti_fps extends check {

		public function __construct() {
		}

		public function employee_add($name) {
		}

		public function message_class_get() {
			return 'HMRC-PAYE-RTI-FPS';
		}

		public function request_body_get_xml() {
			return '<!-- Body -->';
		}

	}

?>