<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class TransactionModel extends CI_Model{
    /**
     * This will authenticate the user
     * @param array payload 
    */
    public function addTransaction($payload){
        $sql_logs = $this->db->set($payload)->get_compiled_insert('transaction');
        return array(
            'sql' => $sql_logs,
        );
    }

    public function updateTransaction($payload,$where){
        $this->db->where($where);
        $sql_logs = $this->db->set($payload)->get_compiled_update('transaction');
        return $sql_logs;
    }

    public function updateUsedStocks($payload,$where){
        $this->db->where($where);
        $sql_logs = $this->db->set($payload)->get_compiled_update('used_stocks');
        return $sql_logs;
    }

    public function addCustomerPlan($payload){
       return $this->db->set($payload)->get_compiled_insert('customer_plans');
    }

    public function addDraftTransaction($payload){
        $sql_logs = $this->db->set($payload)->get_compiled_insert('draf_transaction');
        return array(
            'sql' => $sql_logs,
        );
    }
    public function updateDraftTransaction($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('draf_transaction');
    }
    public function getTransactionForReceipt($payload){
        $sql = "SELECT transaction.transactionid,
                        transaction.ornumber,
                        transaction.cash,
                        transaction.total_price,
                        transaction.data,
                        transaction.note,
                        transaction.discount_amount,
                        CONCAT(users.firstName,' ',users.lastname) as name,
                        stores.storeName,
                        stores.address as store_address,
                        stores.email,
                        stores.telephone,
                        stores.contact
                        FROM `transaction`
                LEFT JOIN users ON users.userId  = `transaction`.userid
                LEFT JOIN stores ON stores.storeid  = `transaction`.storeid
                WHERE `transaction`.isActive = 1";
        
        if (!empty($payload['transactionid'])){
            $sql .= " AND `transaction`.transactionid  = {$payload['transactionid']}";
        }
        return  $this->db->query($sql)->result();
    }
    public function addEndTransaction($payload){
        return $this->db->insert('end_transaction',$payload);
    }
    public function getTransaction($payload){
        $sql = "SELECT * FROM `transaction`
                LEFT JOIN users ON users.userId  = `transaction`.userid
                 LEFT JOIN stores ON stores.storeid  = `transaction`.storeid
                WHERE `transaction`.isActive = 1";


        if(isset($payload['transactionid'])){
            $transactionid = !empty($payload['transactionid']) ? $payload['transactionid']: "";
            if($transactionid != ""){
                $sql .= " AND `transaction`.transactionid = {$transactionid}";
            }
        }

        if(isset($payload['ornumber'])){
            $ornumber = !empty($payload['ornumber']) ? $payload['ornumber']: "";
            if($ornumber != ""){
                $sql .= " AND `transaction`.ornumber = {$ornumber}";
            }
        }
        
        if(isset($payload['userid'])){
            $userid = !empty($payload['userid']) ? $payload['userid']: "All";
            if($userid != "All"){
                $sql .= " AND `transaction`.userid = {$userid}";
            }
        }


        $date = isset($payload['date']) ? $payload['date'] : date("Y-m-d");
        if(!empty($date)){
            $sql .= " AND DATE_FORMAT(transactionDate, '%Y-%m-%d') = '{$date}'";
        }

        
        if(isset($payload['storeid'])){
            $storeid = !empty($payload['storeid']) ? $payload['storeid']: "All";
            if($storeid != "All"){
                $sql .= " AND `transaction`.storeid = {$storeid}";
            }
        }
      
        return  $this->db->query($sql)->result();
    }

    public function getDraftTransaction($payload){
        $sql = "SELECT *,CONCAT(users.firstName,' ',users.lastname) as name FROM `draf_transaction`
                LEFT JOIN users ON users.userId  = `draf_transaction`.userid
                WHERE `draf_transaction`.status = 'Active'";

        if(isset($payload['userid'])){
            $userid = !empty($payload['userid']) ? $payload['userid']: "All";
            if($userid != "All"){
                $sql .= " AND `draf_transaction`.userid = {$userid}";
            }
        }


       
        if(!empty($payload['date'])){
            $date =  $payload['date'];
            $sql .= " AND DATE_FORMAT(draftransactionDate, '%Y-%m-%d') = '{$date}'";
        }


        if(isset($payload['storeid'])){
            $storeid = !empty($payload['storeid']) ? $payload['storeid']: "All";
            if($storeid != "All"){
                $sql .= " AND `draf_transaction`.storeid = {$storeid}";
            }
        }
      
        return  $this->db->query($sql)->result();
    }
    public function getEndTransaction($payload){
        $sql = "SELECT end_transaction.end_transaction_id,
                end_transaction.cash,
                end_transaction.denomonation,
                end_transaction.transactions,
                end_transaction.userid,
                end_transaction.end_transaction_date,
                end_transaction.is_active,
                CONCAT(users.firstName,' ',users.lastname) as name,
                stores.storeName,stores.address as store_address 
                FROM `end_transaction`
                LEFT JOIN users ON users.userId  = `end_transaction`.userid
                LEFT JOIN stores ON stores.storeid  = `users`.storeid
                WHERE `end_transaction`.is_active = 1";

            if(isset($payload['userid'])){
                $userid = !empty($payload['userid']) ? $payload['userid']: "All";
                if($userid != "All"){
                    $sql .= " AND `end_transaction`.userid = {$userid}";
                }
            }


            $date = isset($payload['date']) ? $payload['date'] : date("Y-m-d");
            if(!empty($date)){
                $sql .= " AND DATE_FORMAT(end_transaction_date, '%Y-%m-%d') = '{$date}'";
            }


            if(isset($payload['storeid'])){
                $storeid = !empty($payload['storeid']) ? $payload['storeid']: "All";
                if($storeid != "All"){
                    $sql .= " AND `end_transaction`.storeid = {$storeid}";
                }
            }

        
                 
        return  $this->db->query($sql)->result();
    }
    public function addUsedStocks($data,$ornumber){
        $data = json_decode($data);
        $array = array();
        
        foreach ($data as $key => $value) {
            if($value->type == 'transaction'){
                $where = array();
                $where[] = "s.stockid = " . "'$value->stockid'";
                $where[] = "s.isActive = 1";
                $stockData = $this->getStocksPerId($where);
               
                if( sizeof($stockData) > 0 ){
                    $where = array();
                    $where[] = "s.productid = " . $stockData[0]->productid;
                    $where[] = "s.unitid = " . $stockData[0]->unitid;
                    $where[] = "s.storeid = " . $stockData[0]->storeid;
                    $where[] = "s.isActive = 1";

                    $stockData2 = $this->getStocksPerId($where);
                    
                   
                    $quantity = $value->quantity;
                    
                    for ($i=0; $i < sizeof($stockData2); $i++) { 
                        $remaining = floatval($stockData2[$i]->total);
                        
                        if($quantity > 0){
                            if(  $remaining >= $quantity ){
                                $payload = array(
                                    'stockid' => $stockData2[$i]->stockid,
                                    'quantity' => $quantity,
                                    'ornumber' => $ornumber,
                                );
                               
                                $usedStocksSql = $this->db->set($payload)->get_compiled_insert('used_stocks');
                                array_push($array,$usedStocksSql);
                                
                            }  else{
                                $quantity -= $remaining;
                               
                                $payload = array(
                                    'stockid' => $stockData2[$i]->stockid,
                                    'quantity' => $remaining,
                                    'ornumber' => $ornumber,
                                );
                               
                                $usedStocksSql = $this->db->set($payload)->get_compiled_insert('used_stocks');
                                array_push($array,$usedStocksSql);
                            }
                        }else{
                            break;
                        }
                    }
                }
            }
        }
       return $array;
    }
    public function getStocksPerId($where){

        $sql = "SELECT 
                s.id, 
                s.stockid, 
                s.productid, 
                s.selling_price, 
                s.purchase_price, 
                s.quantity, 
                s.unitid, 
                s.supplierId, 
                s.dateCreated, 
                s.dateUpdated, 
                s.storeid, 
                s.expirationDate, 
                s.status, 
                s.isActive, 
                products.productCode, 
                products.product_name, 
                product_type.productType, 
                products.productTypeId, 
                products.image, 
                suppliers.companyName, 
                stores.storeName, 
                unit.unit, 
                unit.abbreviations, 
                pd.length, 
                pd.width, 
                pd.thickness, 
                sd.size, 
                COALESCE(SUM(CASE WHEN used_stocks.isActive = 1 THEN used_stocks.quantity ELSE 0 END), 0) + COALESCE(SUM(CASE WHEN cl.is_active = 1 THEN cl.converted_quantity ELSE 0 END), 0) AS used_quantity, 
                COALESCE(CASE WHEN sd.size IS NOT NULL THEN s.quantity * sd.size ELSE s.quantity END, 0) - (
                    COALESCE(SUM(CASE WHEN used_stocks.isActive = 1 THEN used_stocks.quantity ELSE 0 END), 0) + 
                    COALESCE(SUM(CASE WHEN cl.is_active = 1 THEN 
                        CASE WHEN cl.converted_size IS NOT NULL AND cl.converted_size <> 0 THEN cl.converted_quantity * cl.converted_size 
                        ELSE cl.converted_quantity END 
                    ELSE 0 END), 0) + 
                    COALESCE(SUM(CASE WHEN mcl.is_active = 1 THEN 
                        CASE WHEN mcl.converted_size IS NOT NULL AND cl.converted_size <> 0 THEN mcl.converted_quantity * mcl.converted_size 
                        ELSE mcl.converted_quantity END 
                    ELSE 0 END), 0)
                ) AS total, 
                CASE WHEN ci.stockid IS NOT NULL THEN TRUE ELSE FALSE END AS has_conversion_item, 
                CASE WHEN sd.stockid IS NOT NULL THEN TRUE ELSE FALSE END AS hasDimension, 
                CASE WHEN LOWER(unit.unit) = 'board foot' THEN TRUE ELSE FALSE END AS isConvertable 
            FROM 
                stocks s 
            LEFT JOIN 
                used_stocks ON s.stockid = used_stocks.stockid 
            LEFT JOIN 
                unit ON unit.unitid = s.unitid 
            JOIN 
                products ON s.productid = products.productid 
            JOIN 
                product_type ON products.productTypeId = product_type.productTypeId 
            JOIN 
                suppliers ON s.supplierId = suppliers.supplierId 
            JOIN 
                stores ON s.storeid = stores.storeid 
            LEFT JOIN 
                conversion_item ci ON s.stockid = ci.stockid 
            LEFT JOIN 
                stock_conversion_logs cl ON s.stockid = cl.from_stockid 
            LEFT JOIN 
                mix_product_conversion_logs mcl ON s.stockid = mcl.from_stockid 
            LEFT JOIN 
                stock_dimension sd ON s.stockid = sd.stockid 
            LEFT JOIN 
                product_dimension pd ON s.productid = pd.productid 
            WHERE 
                    s.isActive = 1";
            
            if (!empty($where)) {
                $sql .= " AND " . implode(" AND ", $where); // Join conditions with AND
            }

            $sql .= "   GROUP BY 
                s.stockid, 
                products.productTypeId, 
                stores.storeid 
            HAVING 
                total > 0
            ORDER BY 
                s.id ASC;";
        return  $this->db->query($sql)->result();
    }
    public function getTransactionByYear($year){
        $sql = "SELECT 
                    months.month,
                    COALESCE(SUM(transaction.total_price), 0) AS total
                FROM 
                    (SELECT 1 AS month UNION ALL
                    SELECT 2 UNION ALL
                    SELECT 3 UNION ALL
                    SELECT 4 UNION ALL
                    SELECT 5 UNION ALL
                    SELECT 6 UNION ALL
                    SELECT 7 UNION ALL
                    SELECT 8 UNION ALL
                    SELECT 9 UNION ALL
                    SELECT 10 UNION ALL
                    SELECT 11 UNION ALL
                    SELECT 12) AS months
                LEFT JOIN 
                    transaction ON MONTH(transaction.transactionDate) = months.month
                    AND YEAR(transaction.transactionDate) = '$year'  -- Or specify the year you are interested in
                GROUP BY 
                    months.month
                ORDER BY 
                    months.month";
        return  $this->db->query($sql)->result();
    }
    public function getStockData($stockid){
        $sql = "SELECT * FROM stocks WHERE stocks.stockid = '$stockid'";
        return  $this->db->query($sql)->result();
    }
 }
?>