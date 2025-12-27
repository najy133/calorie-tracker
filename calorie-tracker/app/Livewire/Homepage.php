<?php

namespace App\Livewire;

use Livewire\Component;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use App\Models\Entry;

class Homepage extends Component
{

    public $calories =0;
    public $food;

    public function render() 
    {
        return view('livewire.homepage')
         ->layout('layouts.app');  
    }


    public function mount(){

    }

    public function estimate()
    {
        if (blank($this->food)) return;

        $response = Prism::text()
        ->using(Provider::OpenAI, 'gpt-3.5-turbo')
        ->withPrompt("
        You are a calorie estimation engine.
        
        Rules:
        - Return ONLY an integer.
        - If quantity is missing, assume a standard serving.
        - If input is not food, return 0.
        
        Input: {$this->food}
        ")
        ->asText()
        ->text;

        $this->calories = max(0, (int) trim($response));
    }

    public function save(){
        if ($this->calories <= 0 || blank($this->food)) return;

        Entry::create([
            'food' => $this->food,
            'calories' => $this->calories,
        ]);

        $this->reset('food', 'calories');
    }
}
