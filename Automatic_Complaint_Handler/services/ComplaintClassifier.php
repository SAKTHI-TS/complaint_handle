<?php

class ComplaintClassifier {
    private $keywords = [
        'Water' => [
            'water', 'pipeline', 'tap', 'drainage', 'sewage', 'leak', 
            'water bill', 'water connection', 'water meter', 'drinking',
            'tank', 'supply', 'pressure', 'dirty water'
        ],
        'Education' => [
            'school', 'college', 'university', 'teacher', 'student', 
            'education', 'classroom', 'admission', 'scholarship',
            'books', 'sports', 'benches', 'toilet', 'renovation'
        ],
        'Electricity' => [
            'electricity', 'power', 'voltage', 'current', 'electric',
            'transformer', 'wire', 'meter', 'bulb', 'light', 'shock',
            'bill', 'connection', 'outage', 'fluctuation'
        ]
    ];

    public function classify($text) {
        $text = strtolower($text);
        $scores = [
            'Water' => 0,
            'Education' => 0,
            'Electricity' => 0
        ];

        // Count keyword matches for each department
        foreach ($this->keywords as $department => $departmentKeywords) {
            foreach ($departmentKeywords as $keyword) {
                if (strpos($text, strtolower($keyword)) !== false) {
                    $scores[$department]++;
                }
            }
        }

        // Get department with highest score
        arsort($scores);
        $department = key($scores);
        
        // If no keywords matched, return default department
        if ($scores[$department] === 0) {
            return 'Water';
        }
        
        return $department;
    }
}
