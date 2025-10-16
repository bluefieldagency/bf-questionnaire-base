<?php

/**
 * Hosting Security Checker
 * 
 * Checks hosting security configuration for DigitalOcean/TransIP VPS
 * ISO 27001 A.13.1.1 - Network controls management
 */

class HostingSecurityChecker {
    
    private $targetUrl;
    private $findings = [];
    
    public function __construct($targetUrl) {
        $this->targetUrl = rtrim($targetUrl, '/');
    }
    
    public function runChecks() {
        echo "🔍 Checking hosting security configuration...\n";
        
        $this->checkHttpsRedirect();
        $this->checkTlsConfiguration();
        $this->checkDatabaseExposure();
        $this->checkRedisExposure();
        $this->checkEnvironmentFileExposure();
        $this->checkBackupFileExposure();
        $this->checkDirectoryListing();
        $this->checkServerInformation();
        
        return $this->findings;
    }
    
    private function checkHttpsRedirect() {
        echo "Checking HTTPS redirect...\n";
        
        $httpUrl = str_replace('https://', 'http://', $this->targetUrl);
        
        $response = $this->makeRequest($httpUrl);
        
        if ($response['status_code'] !== 301 && $response['status_code'] !== 302) {
            $this->addFinding(
                'HIGH',
                'HTTP not redirected to HTTPS',
                'HTTP requests are not automatically redirected to HTTPS, allowing unencrypted communication.',
                'A.13.1.1'
            );
        } else {
            $location = $response['headers']['location'] ?? '';
            if (!str_starts_with($location, 'https://')) {
                $this->addFinding(
                    'MEDIUM',
                    'Improper HTTPS redirect',
                    'HTTP redirect does not properly redirect to HTTPS.',
                    'A.13.1.1'
                );
            }
        }
    }
    
    private function checkTlsConfiguration() {
        echo "Checking TLS configuration...\n";
        
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false, // For testing purposes
                'verify_peer_name' => false
            ]
        ]);
        
        $url = parse_url($this->targetUrl);
        $host = $url['host'];
        $port = $url['scheme'] === 'https' ? 443 : 80;
        
        $socket = @stream_socket_client(
            "ssl://$host:$port",
            $errno, $errstr, 10,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if (!$socket) {
            $this->addFinding(
                'HIGH',
                'TLS Connection Failed',
                "Unable to establish TLS connection: $errstr",
                'A.13.1.1'
            );
            return;
        }
        
        $params = stream_context_get_params($socket);
        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        
        if ($cert) {
            $certInfo = openssl_x509_parse($cert);
            
            // Check certificate expiry
            $validTo = $certInfo['validTo_time_t'];
            $daysToExpiry = ceil(($validTo - time()) / (24 * 60 * 60));
            
            if ($daysToExpiry < 30) {
                $this->addFinding(
                    'MEDIUM',
                    'TLS Certificate Expiring Soon',
                    "TLS certificate expires in $daysToExpiry days.",
                    'A.13.1.1'
                );
            }
            
            // Check for weak signature algorithms
            $signatureAlgorithm = $certInfo['signatureTypeSN'] ?? '';
            if (in_array(strtolower($signatureAlgorithm), ['md5', 'sha1'])) {
                $this->addFinding(
                    'HIGH',
                    'Weak TLS Certificate Signature',
                    "TLS certificate uses weak signature algorithm: $signatureAlgorithm",
                    'A.10.1.1'
                );
            }
        }
        
        fclose($socket);
    }
    
    private function checkDatabaseExposure() {
        echo "Checking database exposure...\n";
        
        // Common database ports
        $dbPorts = [3306, 5432, 27017, 6379];
        $host = parse_url($this->targetUrl)['host'];
        
        foreach ($dbPorts as $port) {
            $socket = @fsockopen($host, $port, $errno, $errstr, 2);
            if ($socket) {
                fclose($socket);
                $this->addFinding(
                    'HIGH',
                    'Database Port Exposed',
                    "Database port $port is accessible from external network.",
                    'A.13.1.1'
                );
            }
        }
    }
    
    private function checkRedisExposure() {
        echo "Checking Redis exposure...\n";
        
        $host = parse_url($this->targetUrl)['host'];
        $socket = @fsockopen($host, 6379, $errno, $errstr, 2);
        
        if ($socket) {
            fclose($socket);
            $this->addFinding(
                'HIGH',
                'Redis Port Exposed',
                'Redis port 6379 is accessible from external network.',
                'A.13.1.1'
            );
        }
    }
    
    private function checkEnvironmentFileExposure() {
        echo "Checking environment file exposure...\n";
        
        $envFiles = [
            '/.env',
            '/.env.production',
            '/.env.local',
            '/.env.backup',
            '/.env.example',
            '/config/.env'
        ];
        
        foreach ($envFiles as $file) {
            $url = $this->targetUrl . $file;
            $response = $this->makeRequest($url);
            
            if ($response['status_code'] === 200) {
                $severity = 'HIGH';
                if (strpos($file, 'example') !== false) {
                    $severity = 'LOW'; // .env.example is usually safe
                }
                
                $this->addFinding(
                    $severity,
                    'Environment File Exposed',
                    "Environment file accessible via web: $file",
                    'A.9.4.5'
                );
            }
        }
    }
    
    private function checkBackupFileExposure() {
        echo "Checking backup file exposure...\n";
        
        $backupFiles = [
            '/backup.sql',
            '/database.sql',
            '/dump.sql',
            '/backup.tar.gz',
            '/backup.zip',
            '/site-backup.tar',
            '/.git/config',
            '/.svn/entries'
        ];
        
        foreach ($backupFiles as $file) {
            $url = $this->targetUrl . $file;
            $response = $this->makeRequest($url);
            
            if ($response['status_code'] === 200) {
                $this->addFinding(
                    'HIGH',
                    'Backup File Exposed',
                    "Backup file accessible via web: $file",
                    'A.12.3.1'
                );
            }
        }
    }
    
    private function checkDirectoryListing() {
        echo "Checking directory listing...\n";
        
        $directories = [
            '/storage/',
            '/uploads/',
            '/files/',
            '/backup/',
            '/logs/',
            '/config/'
        ];
        
        foreach ($directories as $dir) {
            $url = $this->targetUrl . $dir;
            $response = $this->makeRequest($url);
            
            if ($response['status_code'] === 200) {
                $body = $response['body'] ?? '';
                if (strpos($body, 'Index of') !== false || strpos($body, '<title>') !== false) {
                    $this->addFinding(
                        'MEDIUM',
                        'Directory Listing Enabled',
                        "Directory listing enabled for: $dir",
                        'A.13.1.1'
                    );
                }
            }
        }
    }
    
    private function checkServerInformation() {
        echo "Checking server information disclosure...\n";
        
        $response = $this->makeRequest($this->targetUrl);
        $headers = $response['headers'] ?? [];
        
        // Check for server header disclosure
        if (isset($headers['server'])) {
            $serverHeader = $headers['server'];
            if (preg_match('/\d+\.\d+/', $serverHeader)) {
                $this->addFinding(
                    'LOW',
                    'Server Version Disclosure',
                    "Server header reveals version information: $serverHeader",
                    'A.14.2.1'
                );
            }
        }
        
        // Check for X-Powered-By header
        if (isset($headers['x-powered-by'])) {
            $this->addFinding(
                'LOW',
                'X-Powered-By Header Disclosure',
                "X-Powered-By header reveals technology stack: {$headers['x-powered-by']}",
                'A.14.2.1'
            );
        }
        
        // Check for PHP version disclosure in headers
        foreach ($headers as $name => $value) {
            if (stripos($name, 'php') !== false || stripos($value, 'php') !== false) {
                $this->addFinding(
                    'LOW',
                    'PHP Version Disclosure',
                    "PHP version information disclosed in headers: $name: $value",
                    'A.14.2.1'
                );
            }
        }
    }
    
    private function makeRequest($url) {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => 5,
                'ignore_errors' => true,
                'follow_location' => false,
                'header' => [
                    'User-Agent: Hosting-Security-Checker/1.0'
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
            'headers' => $headerArray
        ];
    }
    
    private function addFinding($severity, $title, $description, $isoControl) {
        $this->findings[] = [
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'iso_control' => $isoControl,
            'category' => 'Hosting Security',
            'cvss' => $this->calculateCvss($severity)
        ];
        
        echo "  ❌ $severity: $title\n";
    }
    
    private function calculateCvss($severity) {
        switch (strtoupper($severity)) {
            case 'HIGH': return '7.0';
            case 'MEDIUM': return '5.0';
            case 'LOW': return '3.0';
            default: return '0.0';
        }
    }
    
    public function generateReport() {
        $report = "# Hosting Security Check Report\n\n";
        $report .= "**Target:** {$this->targetUrl}\n";
        $report .= "**Date:** " . date('Y-m-d H:i:s') . "\n\n";
        
        if (empty($this->findings)) {
            $report .= "✅ Hosting security configuration appears secure!\n";
        } else {
            $report .= "## Security Issues Found\n\n";
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
    $checker = new HostingSecurityChecker($targetUrl);
    $findings = $checker->runChecks();
    
    echo "\n" . $checker->generateReport();
    
    // Save results for main report
    if (isset($argv[2])) {
        $outputFile = $argv[2];
        file_put_contents($outputFile, json_encode($findings, JSON_PRETTY_PRINT));
    }
}

?>