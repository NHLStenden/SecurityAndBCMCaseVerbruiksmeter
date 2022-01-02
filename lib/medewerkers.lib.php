<?php

include_once 'generalFunctions.lib.php';

class Medewerkers {

    public static $alleEmployeeStatussen = ['P', 'U', 'A'];
    public static $employeeGender = ['M', 'V', 'X'];
    public static  $emailDomain = 'mijn-nrg.nl';

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
         WHERE emp_idEmployee = :id;
    ";
    private $sql_update_mdw_pensioen = "
       UPDATE tbl_medewerkers
          SET emp_datum_uit_dienst = :datumPensioen, 
              emp_status = 'P'
         WHERE emp_idEmployee = :id;
    ";

    private $sql_update_mdw_functie = "
       UPDATE tbl_medewerkers
          SET emp_functie = :functie
         WHERE emp_idEmployee = :id;
    ";

    private $statement_mdw_backoffice = null;
    private $statement_update_mdw_uitdienst = null;
    private $statement_update_mdw_pensioen = null;
    private $statement_update_mdw_functie = null;
    private $statement_new_mdw = null;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->setupPreparedStatements($db);
    }


    function setupPreparedStatements($db)
    {
        $this->statement_mdw_backoffice       = $this->db->prepare($this->sql_new_mdw);
        $this->statement_update_mdw_uitdienst = $this->db->prepare($this->sql_update_mdw_uitdienst);
        $this->statement_update_mdw_pensioen  = $this->db->prepare($this->sql_update_mdw_pensioen);
        $this->statement_update_mdw_functie   = $this->db->prepare($this->sql_update_mdw_functie);
        $this->statement_new_mdw              = $this->db->prepare($this->sql_new_mdw);

        if ($this->statement_new_mdw === false) {
            var_dump($db->errorInfo());
            die("Cannot setup SQL for new Employee");
        }
        if ($this->statement_mdw_backoffice === false) {
            var_dump($db->errorInfo());
            die("Error in query select mdw");
        }
        if ($this->statement_update_mdw_uitdienst === false) {
            var_dump($db->errorInfo());
            die("Error in query update employee (uitdienst)");
        }
        if ($this->statement_update_mdw_pensioen === false) {
            var_dump($db->errorInfo());
            die("Error in query update employee (pensioen)");
        }
        if ($this->statement_update_mdw_functie === false) {
            var_dump($db->errorInfo());
            die("Error in query update employee (functie)");
        }

    }//setupPreparedStatements


    function GenereerMedewerkers($beschikbarefuncties)
    {

        $this->setupPreparedStatements($this->db);

        $voornamen_file = file_get_contents("./datafiles/voornamen.sorted.txt");
        $achternamen_file = file_get_contents("./datafiles/achternamen.sorted.txt");
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
            }
        }
    }// GenereerMedewerkers

    private function executePreparedStatementWithValues($statement, $values) {
        setParameterValues($statement, $values);
        if (! $statement->execute()) {
            var_dump($statement, $values, $this->db->errorInfo());
            die();
        }
    }//executePreparedStatementWithValues

    function updateMedewerkerUitdienst($datum){
        $this->executePreparedStatementWithValues($this->statement_update_mdw_uitdienst, ["datumUitDienst" => $datum]);
    }//updateMedewerkerUitdienst

    function updateMedewerkerPensioen($datum) {
        $this->executePreparedStatementWithValues($this->statement_update_mdw_uitdienst, ["datumPensioen" => $datum]);
    }

    function updateMedewerkerFunctie($functie){
        $this->executePreparedStatementWithValues($this->statement_update_mdw_uitdienst, ["functie" => $functie]);
    }

}//class Medewerkers