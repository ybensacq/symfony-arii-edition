<?php

namespace Arii\ReportBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * RUNDayRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RUNHourRepository extends EntityRepository
{
   public function findRuns($start, $end, $env='*', $app='*',$job_class='*')
    {
        $Filter = [ 'run.date','run.hour' ];   
        if ($env=='*')
            array_push($Filter,'run.env');
        if ($app=='*')
            array_push($Filter,'run.app');
        if ($job_class=='*')
            array_push($Filter,'run.job_class');
        $f = implode(',',$Filter);
        
        $q = $this->createQueryBuilder('run')
              ->Select($f.',sum(run.warnings) as warnings,sum(run.alarms) as alarms,sum(run.acks) as acks,sum(run.executions) as executions')
              ->where('run.date >= :start')
              ->andWhere('run.date < :end')
              ->groupBy($f)
              ->orderBy('run.date,run.hour')
              ->setParameter('start', $start)
              ->setParameter('end', $end);

        if ($env!='*')
            $q->andWhere('run.env = :env')
                ->setParameter('env', $env);
        if ($app!='*')
            $q->andWhere('run.app = :app')
                ->setParameter('app', $app);
        if ($job_class!='*')
            $q->andWhere('run.job_class = :job_class')
                ->setParameter('job_class', $job_class);
      
                        $query = $q->getQuery();
                        print $query->getSQL();
                        print_r($query->getParameters());
                        exit();
                        
        return $q->getQuery()              
            ->getResult();
    }   

   public function findIssues()
   {
        return $this->createQueryBuilder('run')
            ->Where('run.issues is not null')
            ->getQuery()
            ->getResult();
   }   
    
   public function findAlerts($start, $end, $env='*', $app='*',$job_class='*')
    {
        $Filter = [ 'run.date','run.hour' ];   
        if ($env=='*')
            array_push($Filter,'run.env');
        if ($app=='*')
            array_push($Filter,'run.app');
        if ($job_class=='*')
            array_push($Filter,'run.job_class');
        $f = implode(',',$Filter);
        
        $q = $this->createQueryBuilder('run')
              ->Select($f.',sum(run.warnings) as warnings,sum(run.alarms) as alarms,sum(run.acks) as acks')
              ->where('run.date >= :start')
              ->andWhere('run.date < :end')
              ->groupBy($f)
              ->having('sum(run.alarms)>0')
              ->orderBy('run.date,run.hour')
              ->setParameter('start', $start)
              ->setParameter('end', $end);

        if ($env!='*')
            $q->andWhere('run.env = :env')
                ->setParameter('env', $env);
        if ($app!='*')
            $q->andWhere('run.app = :app')
                ->setParameter('app', $app);
        if ($job_class!='*')
            $q->andWhere('run.job_class = :job_class')
                ->setParameter('job_class', $job_class);
/*        
                        $query = $q->getQuery();
                        print $query->getSQL();
                        print_r($query->getParameters());
                        exit();
*/                        
        return $q->getQuery()              
            ->getResult();
    }   
    
   public function findLast()
   {
        return $this->createQueryBuilder('run')
            ->Select('count(run) as NB,max(run.date) as lastUpdate')
            ->getQuery()
            ->getResult();
   }
   
   // Regroupement pour les jours
   public function findRunsByDay($start, $end)
    {
        return $this->createQueryBuilder('run')
              ->Select('run.date,run.spooler_name,run.app,run.env,run.job_class,sum(run.warnings) as warnings,sum(run.alarms) as alarms,sum(run.acks) as acks,sum(run.executions) as executions')
              ->where('run.date >= :start')
              ->andWhere('run.date < :end')
              ->groupBy('run.date,run.spooler_name,run.app,run.env,run.job_class')
              ->orderBy('run.date,run.spooler_name')
              ->setParameter('start', $start)
              ->setParameter('end', $end)
              ->getQuery()
              ->getResult();
    }   
   
   
}