<?php

class Thing extends Eloquent {
    protected $guarded = array();

    public static $rules = array();

    public function documents()
    {
        return $this->hasMany('Document');
    }


    public function activeLoans()
    {
        $loans = array();
        foreach ($this->documents as $doc) {
            foreach ($doc->loans as $loan) {
                $loans[] = $loan;
            }
        }
        return $loans;
    }

    public function allLoans()
    {
        $loans = array();
        foreach ($this->documents as $doc) {
            foreach ($doc->allLoans as $loan) {
                $loans[] = $loan;
            }
        }
        return $loans;
    }

}