<?php
class plugins_attachfile_db
{
    /**
     * @param $config
     * @param bool $params
     * @return mixed|null
     * @throws Exception
     */
    public function fetchData(array $config, array $params = [])
    {
        $sql = '';
        $dateFormat = new component_format_date();

        if ($config['context'] === 'all') {
            switch ($config['type']) {
                case 'pages':
                    $limit = '';
                    if ($config['offset']) {
                        $limit = ' LIMIT 0, ' . $config['offset'];
                        if (isset($config['page']) && $config['page'] > 1) {
                            $limit = ' LIMIT ' . (($config['page'] - 1) * $config['offset']) . ', ' . $config['offset'];
                        }
                    }

                    $sql = "SELECT mom.id_om,mom.date_start_om,
                                    DATE_FORMAT(mom.date_start_om, '%H:%i') AS hour_start_om,
                                    mom.date_end_om,
                                    DATE_FORMAT(mom.date_end_om, '%H:%i') AS hour_end_om,
                                    mom.date_register
                                    
                                    FROM mc_offers_monthly AS mom" . $limit;

                    if (isset($config['search'])) {
                        $cond = '';
                        if (is_array($config['search']) && !empty($config['search'])) {
                            $nbc = 0;
                            foreach ($config['search'] as $key => $q) {
                                if($q !== '') {
                                    $cond .= !$nbc ? ' WHERE ' : 'AND ';

                                    switch ($key) {
                                        case 'id_om':
                                            $cond .= 'mom.' . $key . ' = :' . $q . ' ';
                                            break;
                                        case 'date_register':
                                        case 'date_start_om':
                                        case 'date_end_om':
                                        $dateFormat = new component_format_date();
                                        $q = $dateFormat->date_to_db_format($q);
                                        $cond .= "mom.".$key." LIKE '%".$q."%' ";
                                            break;
                                    }
                                    $nbc++;
                                }
                            }

                            $sql = "SELECT mom.id_om,mom.date_start_om,
                                    DATE_FORMAT(mom.date_start_om, '%H:%i') AS hour_start_om,
                                    mom.date_end_om,
                                    DATE_FORMAT(mom.date_end_om, '%H:%i') AS hour_end_om,
                                    mom.date_register
                                    FROM mc_offers_monthly AS mom
									$cond " . $limit;
                        }
                    }
                    break;
                case 'attach':
                    $sql = 'SELECT * FROM mc_product_attachfile
                            WHERE id_product = :id ORDER BY order_paf ASC';
                    break;
                case 'attachAll':
                    $sql = 'SELECT * FROM mc_product_attachfile ORDER BY id_paf DESC';
                    break;
            }

            try {
                return $sql ? component_routing_db::layer()->fetchAll($sql, $params) : null;
            }
            catch (Exception $e) {
                return 'Exception reçue : '.$e->getMessage();
            }
        }
		elseif ($config['context'] === 'one') {
            switch ($config['type']) {
                case 'nbAttachProduct':
                    $sql = 'SELECT count(id_paf) AS nbfile FROM mc_product_attachfile 
                                WHERE id_product = :id';
                    break;
                case 'productData':
                    $sql = "SELECT mcpc.name_p, mcp.reference_p
						FROM mc_catalog_product AS mcp
						JOIN mc_catalog_product_content AS mcpc ON(mcp.id_product = mcpc.id_product)
						JOIN mc_lang AS lang ON(mcpc.id_lang = lang.id_lang)
						WHERE mcp.id_product = :id AND mcpc.id_lang = :default_lang";
                    break;
                case 'lastAttach':
                    $sql = 'SELECT * FROM mc_product_attachfile ORDER BY id_paf DESC LIMIT 0,1';
                    break;
                case 'attachId':
                    $sql = 'SELECT * FROM mc_product_attachfile
                            WHERE id_paf = :id';
                    break;
            }

            try {
                return $sql ? component_routing_db::layer()->fetch($sql, $params) : null;
            }
            catch (Exception $e) {
                return 'Exception reçue : '.$e->getMessage();
            }
        }
    }

    /**
     * @param array $config
     * @param array $params
     * @return string|true
     */
    public function insert(array $config, array $params = []) {
        if (!is_array($config)) return '$config must be an array';

        $sql = '';

        switch ($config['type']) {
            case 'productAttach':
                $sql = 'INSERT INTO mc_product_attachfile (id_product, name_paf, type_paf, order_paf, date_register)
                        SELECT :id_product, :name_paf, :type_paf, COUNT(id_paf), NOW() FROM mc_product_attachfile WHERE id_product IN ('.$params['id_product'].')';
                break;
        }

        if($sql === '') return 'Unknown request asked';

        try {
            component_routing_db::layer()->insert($sql,$params);
            return true;
        }
        catch (Exception $e) {
            return 'Exception reçue : '.$e->getMessage();
        }
    }

    /**
     * @param $config
     * @param array $params
     * @return bool|string
     */
    public function update(array $config, array $params = []) {
        if (!is_array($config)) return '$config must be an array';

        $sql = '';

        switch ($config['type']) {
            case 'order':
                $sql = 'UPDATE mc_product_attachfile
						SET order_paf = :order_paf
                		WHERE id_paf = :id_paf';
                break;
        }

        if($sql === '') return 'Unknown request asked';

        try {
            component_routing_db::layer()->update($sql,$params);
            return true;
        }
        catch (Exception $e) {
            return 'Exception reçue : '.$e->getMessage();
        }
    }

    /**
     * @param array $config
     * @param array $params
     * @return bool|string
     */
    public function delete(array $config, array $params = []) {

        if (!is_array($config)) return '$config must be an array';
        $sql = '';

        switch ($config['type']) {
            case 'delAttach':
                $sql = 'DELETE FROM mc_product_attachfile 
						WHERE id_paf IN ('.$params['id'].')';
                $params = array();
                break;
        }

        if($sql === '') return 'Unknown request asked';

        try {
            component_routing_db::layer()->delete($sql,$params);
            return true;
        }
        catch (Exception $e) {
            return 'Exception reçue : '.$e->getMessage();
        }
    }
}