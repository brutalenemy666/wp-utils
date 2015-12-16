<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_GForms_Table {

	public $results = '';

	public function __construct() {

	}

	public function get_html() {
		return '
			<table width="99%" cellpadding="1" border="0" bgcolor="#EAEAEA">
				<tbody>
					<tr>
						<td>
							<table width="100%" cellpadding="5" border="0" bgcolor="#FFFFFF">
								<tbody>
									' . $this->results . '
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		';
	}

	public function add_row($field_name, $field_value, $esc_value=true) {
		$this->results .= '
			<tr bgcolor="#EAF2FA">
				<td colspan="2">
					<font style="font-family:sans-serif;font-size:12px">
						<strong>' . esc_html($field_name) . '</strong>
					</font>
				</td>
			</tr>
			<tr bgcolor="#FFFFFF">
				<td width="20">&nbsp;</td>
				<td>
					<font style="font-family:sans-serif;font-size:12px">' . ( $esc_value ? esc_html($field_value) : $field_value ) . '</font>
				</td>
			</tr>
		';

		return $this;
	}

	public function add_separator($field_name, $font_size=14 , $bgcolor='#DDDDDD') {
		$this->results .= '
			<tr bgcolor="' . $bgcolor . '">
				<td colspan="2">
					<font style="text-align:center; font-family:sans-serif;font-size:' . $font_size . 'px"><em>' . $field_name . '</em></font>
				</td>
			</tr>
		';

		return $this;
	}
}
