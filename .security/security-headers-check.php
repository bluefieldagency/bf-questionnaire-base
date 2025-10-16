<?php

/**
 * Security Headers Checker
 * 
 * Tests for proper security headers according to ISO 27001 A.14.1.3
 */

class SecurityHeadersChecker {
    
    private $targetUrl;
    private $findings = [];
    
    public function __construct($targetUrl) {
        $this->targetUrl = rtrim($targetUrl, '/');
    }
    
    public function checkHeaders() {
        echo "🔍 Checking security headers for: {$this->targetUrl}\n";
        
        // Get headers from target URL
        $headers = $this->getHeaders($this->targetUrl);
        
        if (!$headers) {
            $this->addFinding('HIGH', 'Target URL unreachable', 'Cannot connect to target URL for header analysis');
            return $this->findings;
        }
        
        // Check each security header
        $this->checkCSP($headers);
        $this->checkHSTS($headers);
        $this->checkXFrameOptions($headers);
        $this->checkXContentTypeOptions($headers);
        $this->checkReferrerPolicy($headers);
        $this->checkPermissionsPolicy($headers);
        
        return $this->findings;
    }
    
    private function getHeaders($url) {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => 10,
                'ignore_errors' => true
            ]
        ]);
        
        $headers = @get_headers($url, 1, $context);
        return $headers ?: false;
    }
    
    private function checkCSP($headers) {
        $cspHeader = $this->findHeader($headers, ['Content-Security-Policy', 'X-Content-Security-Policy']);
        
        if (!$cspHeader) {
            $this->addFinding(
                'HIGH',
                'Missing Content Security Policy',
                'No CSP header found. This leaves the application vulnerable to XSS attacks.',
                'A.14.1.3'
            );
            return;
        }
        
        // Check for unsafe CSP directives
        if (strpos($cspHeader, 'unsafe-inline') !== false) {
            $this->addFinding(
                'MEDIUM',
                'Unsafe CSP directive',
                'CSP contains unsafe-inline which reduces XSS protection effectiveness.',
                'A.14.1.3'
            );
        }
        
        if (strpos($cspHeader, 'unsafe-eval') !== false) {
            $this->addFinding(
                'MEDIUM',
                'Unsafe CSP directive',
                'CSP contains unsafe-eval which allows dangerous JavaScript execution.',
                'A.14.1.3'
            );
        }
    }
    
    private function checkHSTS($headers) {
        $hstsHeader = $this->findHeader($headers, ['Strict-Transport-Security']);
        
        if (!$hstsHeader) {
            $this->addFinding(
                'MEDIUM',
                'Missing HSTS Header',
                'No HTTP Strict Transport Security header found. This allows downgrade attacks.',
                'A.13.2.1'
            );
            return;
        }
        
        // Check for proper HSTS configuration
        if (!preg_match('/max-age=(\d+)/', $hstsHeader, $matches)) {
            $this->addFinding(
                'LOW',
                'Invalid HSTS Configuration',
                'HSTS header present but max-age not properly configured.',
                'A.13.2.1'
            );
        } else {
            $maxAge = intval($matches[1]);
            if ($maxAge < 31536000) { // Less than 1 year
                $this->addFinding(
                    'LOW',
                    'Short HSTS max-age',
                    'HSTS max-age should be at least 31536000 (1 year) for proper protection.',
                    'A.13.2.1'
                );
            }
        }
    }
    
    private function checkXFrameOptions($headers) {
        $xFrameHeader = $this->findHeader($headers, ['X-Frame-Options']);
        
        if (!$xFrameHeader) {
            $this->addFinding(
                'MEDIUM',
                'Missing X-Frame-Options Header',
                'No X-Frame-Options header found. This allows clickjacking attacks.',
                'A.14.1.3'
            );
            return;
        }
        
        if (!in_array(strtoupper($xFrameHeader), ['DENY', 'SAMEORIGIN'])) {
            $this->addFinding(
                'LOW',
                'Weak X-Frame-Options Configuration',
                'X-Frame-Options should be set to DENY or SAMEORIGIN for optimal protection.',
                'A.14.1.3'
            );
        }
    }
    
    private function checkXContentTypeOptions($headers) {
        $xContentTypeHeader = $this->findHeader($headers, ['X-Content-Type-Options']);
        
        if (!$xContentTypeHeader || strtolower($xContentTypeHeader) !== 'nosniff') {
            $this->addFinding(
                'LOW',
                'Missing X-Content-Type-Options Header',
                'No X-Content-Type-Options: nosniff header found. This allows MIME type sniffing attacks.',
                'A.14.1.3'
            );
        }
    }
    
    private function checkReferrerPolicy($headers) {
        $referrerHeader = $this->findHeader($headers, ['Referrer-Policy']);
        
        if (!$referrerHeader) {
            $this->addFinding(
                'LOW',
                'Missing Referrer-Policy Header',
                'No Referrer-Policy header found. Consider setting for privacy protection.',
                'A.14.1.3'
            );
        }
    }
    
    private function checkPermissionsPolicy($headers) {
        $permissionsHeader = $this->findHeader($headers, ['Permissions-Policy', 'Feature-Policy']);
        
        if (!$permissionsHeader) {
            $this->addFinding(
                'LOW',
                'Missing Permissions-Policy Header',
                'No Permissions-Policy header found. Consider setting to restrict dangerous features.',
                'A.14.1.3'
            );
        }
    }
    
    private function findHeader($headers, $headerNames) {
        foreach ($headerNames as $name) {
            if (isset($headers[$name])) {
                return is_array($headers[$name]) ? $headers[$name][0] : $headers[$name];
            }
            
            // Case-insensitive search
            foreach ($headers as $key => $value) {
                if (strcasecmp($key, $name) === 0) {
                    return is_array($value) ? $value[0] : $value;
                }
            }
        }
        return null;
    }
    
    private function addFinding($severity, $title, $description, $isoControl = 'A.14.1.3') {
        $this->findings[] = [
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'iso_control' => $isoControl,
            'category' => 'Security Headers',
            'cvss' => $this->calculateCvss($severity)
        ];
    }
    
    private function calculateCvss($severity) {
        switch (strtoupper($severity)) {
            case 'HIGH': return '7.5';
            case 'MEDIUM': return '5.0';
            case 'LOW': return '3.0';
            default: return '0.0';
        }
    }
    
    public function generateReport() {
        $report = "# Security Headers Analysis Report\n\n";
        $report .= "**Target:** {$this->targetUrl}\n";
        $report .= "**Date:** " . date('Y-m-d H:i:s') . "\n\n";
        
        if (empty($this->findings)) {
            $report .= "✅ All security headers properly configured!\n";
        } else {
            $report .= "## Findings\n\n";
            foreach ($this->findings as $finding) {
                $icon = $finding['severity'] === 'HIGH' ? '🔴' : ($finding['severity'] === 'MEDIUM' ? '🟡' : '🟢');
                $report .= "### $icon {$finding['title']} ({$finding['severity']})\n\n";
                $report .= "{$finding['description']}\n\n";
                $report .= "**ISO Control:** {$finding['iso_control']}\n";
                $report .= "**CVSS:** {$finding['cvss']}\n\n";
            }
        }
        
        return $report;
    }
}

// CLI usage
if ($argc > 1) {
    $targetUrl = $argv[1];
    $checker = new SecurityHeadersChecker($targetUrl);
    $findings = $checker->checkHeaders();
    
    echo "\n" . $checker->generateReport();
    
    // Save results for main report
    if (isset($argv[2])) {
        $outputFile = $argv[2];
        file_put_contents($outputFile, json_encode($findings, JSON_PRETTY_PRINT));
    }
}

?>