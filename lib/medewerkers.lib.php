<?php

include_once 'generalFunctions.lib.php';
include_once 'dbfunctions.lib.php';

class Medewerkers {

    public static $alleEmployeeStatussen = ['P', 'U', 'A'];
    public static $employeeGender = ['M', 'V', 'X'];
    public static  $emailDomain = 'mijn-nrg.nl';

    public $listOfPersoneelsNrsGenerated = [];

    private $sql_new_mdw = "INSERT INTO tbl_medewerkers (
    emp_achternaam,      
    emp_voornaam,     
    emp_bsn,    
    emp_email,   
    emp_status,  
    emp_personeelsnummer, 
    emp_geslacht,
    emp_functie,
    emp_datum_in_dienst,
    emp_datum_uit_dienst                         
) VALUES (
    :achternaam, :voornaam, :bsn, :mail, :status, :personeelsnr, :geslacht, :functie,:datumInDienst, :datumUitDienst
);
";

    private $sql_select_mdw = "
       SELECT * 
       FROM tbl_medewerkers as m 
    ";
    private $sql_update_mdw_uitdienst = "
       UPDATE tbl_medewerkers
          SET emp_datum_uit_dienst = :datumUitDienst, 
              emp_status = 'U'
         WHERE emp_personeelsnummer = :id;
    ";
    private $sql_update_mdw_pensioen = "
       UPDATE tbl_medewerkers
          SET emp_datum_uit_dienst = :datumPensioen, 
              emp_status = 'P'
         WHERE emp_personeelsnummer = :id;
    ";

    private $sql_update_mdw_functie = "
       UPDATE tbl_medewerkers
          SET emp_functie = :functie
         WHERE emp_personeelsnummer = :id;
    ";

    private $statement_mdw_backoffice = null;
    private $statement_update_mdw_uitdienst = null;
    private $statement_update_mdw_pensioen = null;
    private $statement_update_mdw_functie = null;
    private $statement_new_mdw = null;

    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->setupPreparedStatements();
    }

    function setupPreparedStatements()
    {
        $this->statement_mdw_backoffice       = $this->db->prepare($this->sql_new_mdw);
        $this->statement_update_mdw_uitdienst = $this->db->prepare($this->sql_update_mdw_uitdienst);
        $this->statement_update_mdw_pensioen  = $this->db->prepare($this->sql_update_mdw_pensioen);
        $this->statement_update_mdw_functie   = $this->db->prepare($this->sql_update_mdw_functie);
        $this->statement_new_mdw              = $this->db->prepare($this->sql_new_mdw);

        if ($this->statement_new_mdw === false) {
            var_dump($this->db->errorInfo());
            die("Cannot setup SQL for new Employee");
        }
        if ($this->statement_mdw_backoffice === false) {
            var_dump($this->db->errorInfo());
            die("Error in query select mdw");
        }
        if ($this->statement_update_mdw_uitdienst === false) {
            var_dump($this->db->errorInfo());
            die("Error in query update employee (uitdienst)");
        }
        if ($this->statement_update_mdw_pensioen === false) {
            var_dump($this->db->errorInfo());
            die("Error in query update employee (pensioen)");
        }
        if ($this->statement_update_mdw_functie === false) {
            var_dump($this->db->errorInfo());
            die("Error in query update employee (functie)");
        }

    }//setupPreparedStatements


    function GenereerMedewerkers(array $beschikbarefuncties, $pathToFiles)
    {

        $this->setupPreparedStatements();

        $voornamen_file = file_get_contents($pathToFiles. "voornamen.sorted.txt");
        $achternamen_file = file_get_contents($pathToFiles. "achternamen.sorted.txt");
        $voornamen = explode("\n", $voornamen_file);
        $achternamen = explode("\n", $achternamen_file);
        $count_voornamen = count($voornamen);
        $count_achternamen = count($achternamen);

        foreach ($beschikbarefuncties as $functie) {
            $aantalTeGeneren = $functie["aantal"];
            $functienaam = $functie["naam"];
            $mogelijkeStatussen = $functie["status"];

            for ($i = 0; $i < $aantalTeGeneren; $i++) {
                $voornaam = $voornamen[rand(0, $count_voornamen - 1)];
                $achternaam = $achternamen[rand(0, $count_achternamen - 1)];

                $personeelsnr = crc32($voornaam . $achternaam . rand());
                $bsn = crc32(rand());
                $mail_voornaam = preg_replace("/ /", "-", $voornaam);
                $mail_achternaam = preg_replace("/(.*), (.*)/", '${2}.${1}', $achternaam);
                $mail_achternaam = preg_replace("/ /", "-", $mail_achternaam);

                $mail = "$mail_voornaam.$mail_achternaam@". self::$emailDomain;
                if (count($mogelijkeStatussen) > 1) {
                    $status = gewogenRandomFromArray($mogelijkeStatussen, [10, 30, 101]);
                } else {
                    $status = $mogelijkeStatussen[0];
                }

                $geslacht = self::$employeeGender[rand(0, count(self::$employeeGender) - 1)];
                $datumUitDienst = null;

                if ($status != 'A') {
                    $datumUitDienst = randomMySQLdate(10, 100);
                }
                // zorg dat datum in dienst altijd vóór datum uit dienst ligt.
                $datumInDienst = randomMySQLdate(150, 5000);

                $new_mdw_values = [
                    "achternaam" => $achternaam,
                    "voornaam" => $voornaam,
                    "bsn" => $bsn,
                    "mail" => $mail,
                    "status" => $status,
                    "personeelsnr" => $personeelsnr,
                    "geslacht" => $geslacht,
                    "functie" => $functienaam,
                    "datumInDienst" => $datumInDienst,
                    "datumUitDienst" => $datumUitDienst,
                ];
                $this->executePreparedStatementWithValues($this->statement_new_mdw, $new_mdw_values);

                $this->listOfPersoneelsNrsGenerated[] = $personeelsnr;
            }
        }
    }// GenereerMedewerkers

    private function executePreparedStatementWithValues(PDOStatement $statement, array $values) {
        setParameterValues($statement, $values);
        $result = $statement->execute();
        if ($result != true) {
            var_dump($statement, $values, $statement->errorInfo());
            die();
        }
    }//executePreparedStatementWithValues

    function updateMedewerkerUitdienstInDatabase(string $id, string $datum){
        $this->executePreparedStatementWithValues(
            $this->statement_update_mdw_uitdienst, ["id" => $id, "datumUitDienst" => $datum]);
    }// updateMedewerkerUitdienstInDatabase

    function updateMedewerkerPensioenInDatabase(string $id, string $datum) {
        $this->executePreparedStatementWithValues(
            $this->statement_update_mdw_pensioen, ["id" => $id, "datumPensioen" => $datum]);
    }// updateMedewerkerPensioenInDatabase

    function updateMedewerkerFunctieInDatabase(string $id,string $functie){
        $this->executePreparedStatementWithValues(
            $this->statement_update_mdw_functie, ["id" => $id, "functie" => $functie]);
    }// updateMedewerkerFunctieInDatabase

    /**
     * @param $record {array} key-value pair array representing a record from a PDO-query
     * @param $datum {string} string representing a valid MySQL Date (e.g. 2022-02-27)
     * @return void
     */
    static function updateMedewerkerUitdienst(array &$record, string $datum){
        $record['emp_datum_uit_dienst'] = $datum;
        $record['emp_status'] = 'U';
    }//updateMedewerkerUitdienst

    /**
     * @param $record {array} a key-value pair array representing a record from a PDO-query
     * @param $datum  {string} $datum a string representing a valid MySQL Date (e.g. 2022-02-27)
     * @return void
     */
    static function updateMedewerkerPensioen(array &$record, string $datum) {
        $record['emp_datum_uit_dienst'] = $datum;
        $record['emp_status'] = 'P';
    }

    /**
     * @param $record  {array} a key-value pair array representing a record from a PDO-query
     * @param $functie {string} the new function
     * @return void
     */
    static function updateMedewerkerFunctie(array &$record, string $functie){
        $record['emp_functie'] = $functie;
    }

}//class Medewerkers