<?php
namespace Arii\DSBundle\Service;

class AriiSOS
{
    protected $db;
    protected $sql;
    
    public function __construct (  
            \Arii\CoreBundle\Service\AriiDHTMLX $db, 
            \Arii\CoreBundle\Service\AriiSQL $sql ) {
        $this->db = $db;
        $this->sql = $sql;
    }

/*********************************************************************
 * Informations de connexions
 *********************************************************************/

   public function getJobInfos($job_id) {   
        $dhtmlx = $this->db;
        $data = $dhtmlx->Connector('data');

        $sql = $this->sql;
        $qry = $sql->Select(array('JOB','SCHEDULER_ID'))
                .$sql->From(array('DAYS_SCHEDULE'))
                .$sql->Where(array('ID' => $job_id));
        $res = $data->sql->query( $qry );
        $line = $data->sql->get_next($res);

        return array($line['SCHEDULER_ID'],$line['JOB'],'');        
   }

   public function getTaskInfos($job_id) {   
        $dhtmlx = $this->db;
        $data = $dhtmlx->Connector('data');
       
        // le job_id peut avoir une tâche
        if (($p = strpos($job_id,'#'))>0) {
            $job_id = substr($job_id,0,$p);
        }
        $sql = $this->sql;
        $qry = $sql->Select(array('st.JOB_NAME','st.SPOOLER_ID','st.PARAMETERS'))
                .$sql->From(array('SCHEDULER_TASKS st'))
                .$sql->Where(array('st.TASK_ID' => $job_id));
        $res = $data->sql->query( $qry );
        $line = $data->sql->get_next($res);

        return array($line['SPOOLER_ID'],$line['JOB_NAME'],$line['PARAMETERS']);        
   }

   public function getOrderInfos($id) {   
       $dhtmlx = $this->db;
       $data = $dhtmlx->Connector('data');
        
        $sql = $this->sql;
        $qry = $sql->Select(array('JOB_CHAIN','SCHEDULER_ID','ORDER_ID'))
                .$sql->From(array('DAYS_SCHEDULE'))
                .$sql->Where(array('ID' => $id));
        
        $res = $data->sql->query( $qry );
        $line = $data->sql->get_next($res);
        return array($line['SCHEDULER_ID'],$line['ORDER_ID'],$line['JOB_CHAIN']);
   }

   public function getJobChainInfos($id) {   
        $dhtmlx = $this->db;
        $data = $dhtmlx->Connector('data');

        $sql = $this->sql;
        $qry = $sql->Select(array('soh.JOB_CHAIN','soh.SPOOLER_ID','soh.ORDER_ID'))
                .$sql->From(array('SCHEDULER_ORDER_HISTORY soh'))
                .$sql->Where(array('soh.history_id' => $id));

        $res = $data->sql->query( $qry );
        $line = $data->sql->get_next($res);
        return array($line['SPOOLER_ID'],$line['JOB_CHAIN'],$line['ORDER_ID']);
   }

   public function getStateInfos($id) {   
        $dhtmlx = $this->db;
        $data = $dhtmlx->Connector('data');

        // si l'id est une chaine, c'est dans le SCHEDULER_ORDERS_STEP_HISTORY
        if (strpos($id,'/')>0) {
            $Infos = explode('/',$id);
            $spooler = array_shift($Infos);
            $state = array_pop($Infos);
            $job_chain = implode('/',$Infos);
            return array($spooler,'',$job_chain,$state);
        }
        
        $sql = $this->sql;
        $qry = $sql->Select(array('sosh.STATE','soh.JOB_CHAIN','soh.SPOOLER_ID','soh.ORDER_ID'))
                .$sql->From(array('SCHEDULER_ORDER_STEP_HISTORY sosh'))
                .$sql->LeftJoin('SCHEDULER_ORDER_HISTORY soh',array('sosh.HISTORY_ID','soh.HISTORY_ID'))
                .$sql->Where(array('sosh.TASK_ID'=>$id)); 
        $res = $data->sql->query( $qry );
        $line = $data->sql->get_next($res);
        return array($line['SPOOLER_ID'],$line['ORDER_ID'],$line['JOB_CHAIN'],$line['STATE']);
   }

   public function getConnectInfos($spooler) {
        $dhtmlx = $this->db;
        $data = $dhtmlx->Connector('data');

        // on cherche le scheduler dans la base de données
        $sql = $this->sql;
        $qry = $sql->Select(array('SCHEDULER_ID as SPOOLER_ID','HOSTNAME','TCP_PORT','IS_RUNNING','IS_PAUSED','START_TIME'))
                .$sql->From(array('SCHEDULER_INSTANCES'))
                .$sql->Where(array('SCHEDULER_ID' => $spooler ))
                .$sql->OrderBy(array('ID desc'));

        $res = $data->sql->query( $qry );
        // pourrais etre en parametre si besoin
        $protocol = "http"; $path = "";
        while ($line = $data->sql->get_next($res)) {
            $scheduler = $line['SPOOLER_ID'];
            $hostname = $line['HOSTNAME'];
            $port = $line['TCP_PORT'];
            $start_time = $line['TCP_PORT'];
            if ($line['IS_RUNNING']!=1) {
                // on tente un update ?
            }
            return array($protocol,$spooler,$hostname,$port,$path);  
        }
        // sinon on regarde dans les parametres
                      
   }
   
}
