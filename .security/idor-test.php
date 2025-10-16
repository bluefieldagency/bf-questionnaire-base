<?php

/**
 * Insecure Direct Object Reference (IDOR) Testing
 * 
 * Tests for IDOR vulnerabilities in a non-destructive manner
 * ISO 27001 A.9.2.3 - Management of privileged access rights
 */

class IdorTester {
    
    private $baseUrl;
    private $findings = [];
    private $testResults = [];
    
    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    public function runTests() {
        echo "🔍 Testing for IDOR vulnerabilities (non-destructive)\n";
        
        // Test common IDOR patterns
        $this->testSequentialIds();
        $this->testUuidEnumeration();
        $this->testFileAccess();
        $this->testApiEndpoints();
        
        return $this->findings;
    }
    
    private function testSequentialIds() {
        echo "Testing sequential ID enumeration...\n";
        
        $patterns = [
            '/questionnaire/{id}',
            '/questionnaire/{id}/page/{page_id}',
            '/api/questionnaire/{id}',
            '/admin/questionnaire/{id}',
            '/user/{id}',
            '/entry/{id}'
        ];
        
        foreach ($patterns as $pattern) {
            // Test with sequential IDs (1, 2, 3, etc.)
            for ($i = 1; $i <= 5; $i++) {
                $url = str_replace('{id}', $i, $pattern);
                $url = str_replace('{page_id}', $i, $url);
                $url = $this->baseUrl . $url;
                
                $response = $this->makeRequest($url);
                
                if ($response['status_code'] === 200) {
                    $this->addFinding(
                        'HIGH',
                        'Potential IDOR via Sequential ID',
                        "Accessible resource with sequential ID: $url",
                        'A.9.2.3',
                        $url
                    );
                } elseif ($response['status_code'] === 403) {
                    // Good - access denied
                    $this->testResults[] = "✅ Access properly denied: $url";
                } elseif ($response['status_code'] === 404) {
                    // Resource doesn't exist - normal
                    $this->testResults[] = "ℹ️  Resource not found: $url";
                }
            }
        }
    }
    
    private function testUuidEnumeration() {
        echo "Testing UUID enumeration patterns...\n";
        
        // Common UUID patterns that might be guessable
        $testUuids = [
            '00000000-0000-0000-0000-000000000001',
            '11111111-1111-1111-1111-111111111111',
            'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa'
        ];
        
        $patterns = [
            '/questionnaire/{uuid}',
            '/api/questionnaire/{uuid}',
            '/entry/{uuid}'
        ];
        
        foreach ($patterns as $pattern) {
            foreach ($testUuids as $uuid) {
                $url = str_replace('{uuid}', $uuid, $pattern);
                $url = $this->baseUrl . $url;
                
                $response = $this->makeRequest($url);
                
                if ($response['status_code'] === 200) {
                    $this->addFinding(
                        'MEDIUM',
                        'Potential IDOR via Predictable UUID',
                        "Accessible resource with predictable UUID: $url",
                        'A.9.2.3',
                        $url
                    );
                }
            }
        }
    }
    
    private function testFileAccess() {
        echo "Testing file access controls...\n";
        
        // Test common file paths that might be accessible
        $filePaths = [
            '/storage/app/questionnaires/1.json',
            '/storage/app/uploads/1.pdf',
            '/files/questionnaire/1',
            '/download/entry/1',
            '/export/questionnaire/1'
        ];
        
        foreach ($filePaths as $path) {
            $url = $this->baseUrl . $path;
            $response = $this->makeRequest($url);
            
            if ($response['status_code'] === 200) {
                $this->addFinding(
                    'HIGH',
                    'Direct File Access Vulnerability',
                    "Direct access to file without authentication: $url",
                    'A.9.4.1',
                    $url
                );
            }
        }
    }
    
    private function testApiEndpoints() {
        echo "Testing API endpoint access controls...\n";
        
        $apiEndpoints = [
            '/api/questionnaires',
            '/api/questionnaire/1',
            '/api/entries',
            '/api/entry/1',
            '/api/users',
            '/api/user/1',
            '/api/admin/stats'
        ];
        
        foreach ($apiEndpoints as $endpoint) {
            $url = $this->baseUrl . $endpoint;
            
            // Test without authentication
            $response = $this->makeRequest($url);
            
            if ($response['status_code'] === 200) {
                $contentType = $response['headers']['content-type'] ?? '';
                
                if (strpos($contentType, 'application/json') !== false) {
                    $this->addFinding(
                        'HIGH',
                        'Unauthenticated API Access',
                        "API endpoint accessible without authentication: $url",
                        'A.9.2.1',
                        $url
                    );
                }
            }
            
            // Test with invalid/manipulated IDs
            if (strpos($endpoint, '/1') !== false) {
                $testUrl = str_replace('/1', '/999999', $url);
                $response = $this->makeRequest($testUrl);
                
                if ($response['status_code'] === 200) {
                    $this->addFinding(
                        'MEDIUM',
                        'API IDOR Vulnerability',
                        "API allows access to arbitrary IDs: $testUrl",
                        'A.9.2.3',
                        $testUrl
                    );
                }
            }
        }
    }
    
    private function makeRequest($url) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true,
                'header' => [
                    'User-Agent: IDOR-Tester/1.0',
                    'Accept: application/json, text/html'
                ]
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        
        // Parse status code
        $statusCode = 0;
        if (!empty($headers[0])) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0], $matches);
            $statusCode = intval($matches[1] ?? 0);
        }
        
        // Parse headers into array
        $headerArray = [];
        foreach ($headers as $header) {
            if (strpos($header, ':') !== false) {
                list($key, $value) = explode(':', $header, 2);
                $headerArray[strtolower(trim($key))] = trim($value);
            }
        }
        
        return [
            'status_code' => $statusCode,
            'body' => $response,
            'headers' => $headerArray,
            'url' => $url
        ];
    }
    
    private function addFinding($severity, $title, $description, $isoControl, $url) {
        $this->findings[] = [
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'iso_control' => $isoControl,
            'category' => 'Access Control',
            'cvss' => $this->calculateCvss($severity),
            'url' => $url,
            'test_type' => 'IDOR'
        ];
        
        echo "  ❌ $severity: $title - $url\n";
    }
    
    private function calculateCvss($severity) {
        switch (strtoupper($severity)) {
            case 'HIGH': return '8.5';
            case 'MEDIUM': return '6.0';
            case 'LOW': return '3.0';
            default: return '0.0';
        }
    }
    
    public function generateReport() {
        $report = "# IDOR Testing Report\n\n";
        $report .= "**Target:** {$this->baseUrl}\n";
        $report .= "**Date:** " . date('Y-m-d H:i:s') . "\n";
        $report .= "**Test Type:** Non-destructive IDOR enumeration\n\n";
        
        if (empty($this->findings)) {
            $report .= "✅ No IDOR vulnerabilities detected!\n";
        } else {
            $report .= "## Vulnerabilities Found\n\n";
            foreach ($this->findings as $finding) {
                $icon = $finding['severity'] === 'HIGH' ? '🔴' : ($finding['severity'] === 'MEDIUM' ? '🟡' : '🟢');
                $report .= "### $icon {$finding['title']} ({$finding['severity']})\n\n";
                $report .= "**URL:** `{$finding['url']}`\n\n";
                $report .= "{$finding['description']}\n\n";
                $report .= "**ISO Control:** {$finding['iso_control']}\n";
                $report .= "**CVSS:** {$finding['cvss']}\n\n";
                $report .= "**Mitigation:** Implement proper authorization checks and use non-sequential, non-predictable identifiers.\n\n";
            }
        }
        
        $report .= "## Test Results Summary\n\n";
        foreach ($this->testResults as $result) {
            $report .= "- $result\n";
        }
        
        return $report;
    }
}

// CLI usage
if ($argc > 1) {
    $targetUrl = $argv[1];
    $tester = new IdorTester($targetUrl);
    $findings = $tester->runTests();
    
    echo "\n" . $tester->generateReport();
    
    // Save results for main report
    if (isset($argv[2])) {
        $outputFile = $argv[2];
        file_put_contents($outputFile, json_encode($findings, JSON_PRETTY_PRINT));
    }
}

?>