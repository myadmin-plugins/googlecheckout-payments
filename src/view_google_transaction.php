<?php

	function view_google_transaction() {
		if ($GLOBALS['tf']->ima == 'admin') {
			$db = clone $GLOBALS['tf']->db;
			$module = get_module_name((isset($GLOBALS['tf']->variables->request['module']) ? $GLOBALS['tf']->variables->request['module'] : 'default'));
			$transaction = $db->real_escape($GLOBALS['tf']->variables->request['transaction']);
			$db->query("select * from gcheckout where google_order='{$transaction}'");
			if ($db->num_rows() == 0) {
				$db = get_module_db($module);
				$db->query("select * from gcheckout where google_order='{$transaction}'");
			}
			if ($db->num_rows() > 0) {

				/**
				 * @param $table
				 * @param $data
				 * @return bool|string|\TFTable
				 * @throws \Exception
				 * @throws \SmartyException
				 */
				function google_table($table, $data) {
					//echo '<pre>';print_r($data);echo '</pre>';
					if (is_bool($table) && $table === false) {
						$started = true;
						$table = new TFTable;
						$table->hide_title();
					} else
						$started = false;
					foreach ($data as $key => $value)
						if (null !== $value && (is_array($value) || trim($value) != '')) {
							$table->set_col_options('style="vertical-align: top;"');
							$table->add_field(ucwords(str_replace('_', ' ', $key)), 'r');
							if (is_array($value))
								$table->add_field(google_table(false, $value));
							elseif (preg_match('/^a:[0-9]+:{.*}$/s', $value))
								$table->add_field(google_table(false, myadmin_unstringify($value)));
							elseif ($key == 'lid')
								$table->add_field($table->make_link('choice=none.search&amp;search='.$value, $value), 'r');
							else
								$table->add_field($value, 'r');
							$table->add_row();
						}
					if ($started === true) {
						return $table->get_table();
					} else {
						return $table;
					}
				}

				while ($db->next_record(MYSQL_ASSOC)) {
					$table = new TFTable;
					$table->set_title('Transaction Information');
					$table = google_table($table, $db->Record);
					//var_dump($table);exit;
					add_output($table->get_table());
				}
			}
		} else {
			add_output('This functionality is for administrators only');
		}
	}
