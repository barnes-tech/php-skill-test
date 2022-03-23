<?php

namespace App\Service;

class CompanyMatcher
{
    private $db;
    private $matches = [];
    private $pickedMatches = [];


    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function match(String $prefix, Int $rooms, String $type)
    {
        //I am aware this may be easier to implement with DOCTRINE
        $stmt = $this->db->prepare("SELECT * FROM company_matching_settings");
        $stmt->execute();
        $matcher = $stmt->fetchAll();
        //loop through companies
        $matchIds = [];

        foreach($matcher as $match)
        {
          //postcode array
          $id = $match['company_id'];
          $postcodeArr = json_decode($match['postcodes']);
          $bedroomArr = json_decode($match['bedrooms']);
          $typeCheck = $match['type'];
          //check prefix is in postcodes
          if(in_array($prefix, $postcodeArr))
          {
            if(in_array($rooms,$bedroomArr))
            {
              if($type == $typeCheck)
              {
                $matchIds[]=$id;
              }
            }
          }
        }
        // loop through matching company id to query company table
        foreach($matchIds as $id) {
          $stmt = $this->db->prepare("SELECT * FROM companies WHERE id=:id");
          $stmt->execute(['id'=>$id]);
          $company = $stmt->fetch();
          $this->matches[]= $company;
        }
    }

    public function pick(int $count)
    {

      $limit = count($this->matches);
      $max = $count > $limit ? $limit : $count;
      $i = 0;
      shuffle($this->matches);
      while($i < $max)
      {
        $this->pickedMatches[] = $this->matches[$i];
        $i++;
      }
    }

    public function results(): array
    {
        return $this->pickedMatches;
    }

    public function deductCredits()
    {
      foreach($this->pickedMatches as $company)
      {
        $credits = $company['credits'];
        $newCredits = $credits--;

        $stmt = $this->db->prepare("UPDATE companies SET credits=:credits WHERE id=:id");
        $stmt->execute(['credits'=> $newCredits,'id'=>$company['id']]);
      }
    }
}
