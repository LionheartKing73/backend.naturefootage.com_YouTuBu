<?php

class Locations_model extends CI_Model
{
    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
    }

	function get_all($lang = 'en', $used_only = false)
	{
		$sql = $used_only ?
			'SELECT DISTINCT lc.name, l.id, l.parent_id, l2.parent_id grandparent_id, c.active
      FROM lib_locations l
      LEFT JOIN lib_locations l2 ON l2.id = l.parent_id
      LEFT JOIN lib_locations_content lc ON lc.location_id = l.id AND lc.lang = ?
      LEFT JOIN lib_clips c ON c.location_id IN (l.id, l.parent_id, l2.parent_id)
      ORDER BY grandparent_id, parent_id, name' :
			'SELECT lc.name, l.id, l.parent_id, l2.parent_id grandparent_id
      FROM lib_locations l
      LEFT JOIN lib_locations l2 ON l2.id = l.parent_id
      LEFT JOIN lib_locations_content lc ON lc.location_id = l.id AND lc.lang = ?
      ORDER BY grandparent_id, parent_id, name';

		$list = $this->db->query($sql, array($lang))->result_array();

		$result = array();

		foreach ($list as $location)
		{
			if ( ! $location['parent_id'])
			{
				$result[$location['id']]['name'] = $location['name'];
				if ($used_only && $location['active'])
				{
					$result[$location['id']]['active'] = 1;
				}
			}
			elseif ( ! $location['grandparent_id'])
			{
				$result[$location['parent_id']]['provinces'][$location['id']]['name'] = $location['name'];
				if ($used_only && $location['active'])
				{
					$result[$location['parent_id']]['provinces'][$location['id']]['active'] = 1;
				}
			}
			else
			{
				$result[$location['grandparent_id']]['provinces'][$location['parent_id']]['cities'][$location['id']]
					= $location['name'];
				if ($used_only)
				{
					if ($location['active'])
					{
						$result[$location['grandparent_id']]['provinces'][$location['parent_id']]['active'] = 1;
						$result[$location['grandparent_id']]['active'] = 1;
					}
					else
					{
						unset($result[$location['grandparent_id']]['provinces'][$location['parent_id']]['cities'][$location['id']]);
					}
				}
			}
		}

		return $result;
	}

	function get_list($parent_id = 0, $lang = 'en')
	{
		$list = $this->db->query(
				'SELECT l.id, lc.name
      FROM lib_locations l
      LEFT JOIN lib_locations_content lc ON lc.location_id = l.id AND lc.lang = ?
      WHERE l.parent_id = ? ORDER BY lc.name', array($lang, $parent_id))->result_array();
		return $list;
	}

	function get($id, $lang = 'en')
	{
		$row = $this->db->query(
				'SELECT l.*, lc.name
      FROM lib_locations l
      LEFT JOIN lib_locations_content lc ON lc.location_id = l.id AND lc.lang = ?
      WHERE l.id = ?', array($lang, $id))->result_array();
		return $row[0];
	}

	function get_parent_id($id)
	{
		$row = $this->db->query(
				'SELECT parent_id
      FROM lib_locations
      WHERE id = ?', array($id))->result_array();
		return $row[0]['parent_id'];
	}

	function add($name, $lang = 'en', $parent_id = 0)
	{
		$parent_id = intval($parent_id);
		$this->db_master->insert('lib_locations', array('parent_id' => $parent_id));
		$id = $this->db_master->insert_id();

		if ($id)
		{
			$this->db_master->insert('lib_locations_content', array('location_id' => $id, 'lang' => $lang, 'name' => $name));
		}

		return $id;
	}

	function update($id, $name, $lang = 'en', $parent_id = 0)
	{
		$id = intval($id);
		$parent_id = intval($parent_id);

		$this->db_master->query('UPDATE lib_locations SET parent_id = ? WHERE id = ?', array($parent_id, $id));

		$row = $this->db->query('SELECT id FROM lib_locations_content WHERE location_id = ? AND lang = ?', array($id, $lang))->result_array();

		if (count($row))
		{
			$this->db_master->query(
				'UPDATE lib_locations_content SET name = ? WHERE location_id = ? AND lang = ?', array($name, $id, $lang));
		}
		else
		{
			$this->db_master->query(
				'INSERT INTO lib_locations_content (name, location_id, lang) VALUES (?, ?, ?)', array($name, $id, $lang));
		}
	}

	function delete($id)
	{
		$ids = array($id);

		$children = $this->db->query('SELECT id FROM lib_locations WHERE parent_id=?', $ids)->result_array();

		if (count($children))
		{
			foreach ($children as $child)
			{
				$ids[] = $child['id'];
			}

			unset($ids[0]);
			$ids_str = implode(', ', $ids);
			$ids[0] = $id;

			$children = $this->db->query(
					'SELECT id FROM lib_locations WHERE parent_id IN (' . $ids_str . ')')->result_array();
			if (count($children))
			{
				foreach ($children as $child)
				{
					$ids[] = $child['id'];
				}
			}
		}

		$ids_str = implode(', ', $ids);
		$this->db_master->query('DELETE FROM lib_locations_content WHERE location_id IN (' . $ids_str . ')');
		$this->db_master->query('DELETE FROM lib_locations WHERE id IN (' . $ids_str . ')');
	}

	function get_location_string($id, $lang = 'en')
	{
		$sql = 'SELECT lc.location_id, lc.name, l.parent_id
      FROM lib_locations_content lc
      INNER JOIN lib_locations l ON l.id = lc.location_id
      WHERE lc.location_id = ? AND lc.lang = ?';

		$row = $this->db->query($sql, array($id, $lang))->result_array();
		$row = $row[0];

		$str = $row['name'];

		if ($row['parent_id'])
		{
			$row = $this->db->query($sql, array($row['parent_id'], $lang))->result_array();
			$row = $row[0];

			$str .= ', ' . $row['name'];
		}

		if ($row['parent_id'])
		{
			$row = $this->db->query($sql, array($row['parent_id'], $lang))->result_array();
			$row = $row[0];

			$str .= ', ' . $row['name'];
		}

		$str = str_replace(' no province, ', '', $str);
		return $str;
	}

	function get_filter($id)
	{
		$filter = array($id);
		$children = $this->db->query('SELECT id FROM lib_locations WHERE parent_id = ?', $id)
			->result_array();

		if (count($children))
		{
			foreach ($children as $row)
			{
				$filter[] = $row['id'];
			}

			$in = $filter;
			unset($in[0]);

			$grandchildren = $this->db->query('SELECT id FROM lib_locations
        WHERE parent_id IN (' . implode(', ', $in) . ')')->result_array();
			if ($grandchildren)
			{
				foreach ($grandchildren as $row)
				{
					$filter[] = $row['id'];
				}
			}
		}

		return implode(', ', $filter);
	}

	function parse_location_string($str) {

		$location_parts = explode(',', $str);
		foreach ($location_parts as &$part) {
			$part = trim($part);
		}
		$location_parts = array_reverse($location_parts);

		$location_id = 0;
		foreach ($location_parts as $i=>$location_name) {

			$where = array('lc.name' => $location_name);
			if ($location_id) {
				$where['l.parent_id'] = $location_id;
			}

			$data = $this->db->
				select('lc.location_id')->
				from('lib_locations_content lc')->
				join('lib_locations l', 'l.id = lc.location_id')->
				where($where)->
				order_by('location_id')->
				get()->result();

			if (count($data) > 1) {
				$location_id = $data[0]->location_id;
			}
			if (count($data) == 1) {
				$location_id = $data[0]->location_id;
			}
			elseif (count($data) == 0) {
				$this->db_master->insert('lib_locations',
					array('parent_id'=>$location_id));
				$location_id = $this->db_master->insert_id();
				$this->db_master->insert('lib_locations_content', array(
					'location_id'=>$location_id, 'lang'=>'en', 'name'=>$location_name));
			}
		}

		return $location_id;
	}

}
