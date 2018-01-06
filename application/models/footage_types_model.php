<?php

class Footage_Types_Model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

	function get_list($lang = 'en') {
		$list = $this->db->query('SELECT ft.id, ftc.name
		FROM lib_footage_types ft
		LEFT JOIN lib_footage_types_content ftc ON ftc.footage_type_id = ft.id AND lang=?
		ORDER BY ftc.name', $lang)->result();
		return $list;
	}

	function update($id, $name, $lang = 'en') {
		$this->db_master->query(
			'UPDATE lib_footage_types_content
			SET name=?
			WHERE id=? AND lang=?',
			array($name, $id, $lang));
	}

	function insert($name, $lang = 'en') {
		$this->db_master->query('INSERT INTO lib_footage_types VALUES()');
		$id = $this->db_master->insert_id();
		$this->db_master->insert('lib_footage_types_content',
			array('footage_type_id' => $id, 'lang' => $lang, 'name' => $name));
	}

	function delete($id) {
		$this->db_master->update('lib_clips', array('footage_type'=>0), array('footage_type'=>$id));
		$this->db_master->delete('lib_footage_types_content', array('footage_type' => $id));
		$this->db_master->delete('lib_footage_types', array('id' => $id));
	}

	function get_footage_type_ids($str_list) {
		$ids = array();

		if (count($str_list)) {
			$params = substr(str_repeat('?,', count($str_list)), 0, -1);
			$sql = 'SELECT footage_type_id id FROM lib_footage_types_content WHERE name IN ('
				. $params . ')';
			$rows = $this->db->query($sql, $str_list)->result_array();
			if ($rows) {
				foreach ($rows as $row) {
					$ids[] = $row['id'];
				}
			}
		}

		return $ids;
	}

}
