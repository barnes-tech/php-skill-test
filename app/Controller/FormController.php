<?php

namespace App\Controller;

use App\Service\CompanyMatcher;

//use Symfony\HttpFoundation\Request;

class FormController extends Controller
{
    public function index()
    {
        $this->render('form.twig');
    }

    public function submit()
    {
        //escape html characters
        $postcode = htmlentities($_POST['postcode'],ENT_QUOTES,"UTF-8");
        $bedrooms = htmlentities($_POST['bedrooms'],ENT_QUOTES,"UTF-8");
        $type = htmlentities($_POST['type'],ENT_QUOTES,"UTF-8");
        //regular expression to find postcode prefix
        $prefix = preg_replace('#^([a-z]+).*#i','$1',$postcode);
        $prefix = mb_strtoupper($prefix);


        $matcher = new CompanyMatcher($this->db());
        //all matches
        $matcher->match($prefix ,$bedrooms, $type);
        //limit matches
        $matcher->pick(3);
        //decuct credits from selected matches
        $matcher->deductCredits();
        //return the results
        $matchedCompanies = $matcher->results();

        $this->render('results.twig', [
            'matchedCompanies'  => $matchedCompanies,
        ]);
    }
}
