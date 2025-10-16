<?php

/**
 * Penetration Test Report Generator
 * 
 * Generates comprehensive security report according to ISO 27001 guidelines
 * Maps findings to Annex A controls and includes CVSS scoring
 */

if ($argc < 2) {
    echo "Usage: php generate-report.php <reports_directory>\n";
    exit(1);
}

$reportsDir = $argv[1];
$reportFile = $reportsDir . '/pentest-findings.md';

// ISO 27001 Annex A Controls mapping
$iso27001Controls = [
    'A.9.2.3' => 'Management of privileged access rights',
    'A.9.4.2' => 'Secure log-on procedures',
    'A.9.4.3' => 'Password management system',
    'A.10.1.1' => 'Policy on the use of cryptographic controls',
    'A.12.4.1' => 'Event logging',
    'A.12.6.1' => 'Management of technical vulnerabilities',
    'A.13.1.3' => 'Separation of networks',
    'A.14.1.3' => 'Protection of application services transactions',
    'A.14.2.1' => 'Secure development policy',
    'A.14.2.5' => 'Secure system engineering principles'
];

$findings = [];
$riskSummary = ['HIGH' => 0, 'MEDIUM' => 0, 'LOW' => 0];

echo "Generating comprehensive security report...\n";

// Parse Semgrep results
$semgrepFile = $reportsDir . '/semgrep-results.json';
if (file_exists($semgrepFile)) {
    $semgrepData = json_decode(file_get_contents($semgrepFile), true);
    if (isset($semgrepData['results'])) {
        foreach ($semgrepData['results'] as $result) {
            $severity = strtoupper($result['extra']['severity']);
            $riskSummary[$severity]++;
            
            $findings[] = [
                'title' => $result['check_id'],
                'severity' => $severity,
                'description' => $result['extra']['message'],
                'file' => $result['path'] . ':' . $result['start']['line'],
                'iso_control' => $result['extra']['metadata']['iso27001'] ?? 'Not mapped',
                'cvss' => $result['extra']['metadata']['cvss'] ?? 'N/A',
                'category' => 'Static Code Analysis'
            ];
        }
    }
}

// Parse OWASP Semgrep results
$owaspFile = $reportsDir . '/semgrep-owasp.json';
if (file_exists($owaspFile)) {
    $owaspData = json_decode(file_get_contents($owaspFile), true);
    if (isset($owaspData['results'])) {
        foreach ($owaspData['results'] as $result) {
            $severity = strtoupper($result['extra']['severity']);
            $riskSummary[$severity]++;
            
            $findings[] = [
                'title' => $result['check_id'],
                'severity' => $severity,
                'description' => $result['extra']['message'],
                'file' => $result['path'] . ':' . $result['start']['line'],
                'iso_control' => mapOwaspToIso($result['check_id']),
                'cvss' => calculateCvss($severity),
                'category' => 'OWASP Top 10'
            ];
        }
    }
}

// Parse Composer audit results
$composerFile = $reportsDir . '/composer-audit.json';
if (file_exists($composerFile)) {
    $composerData = json_decode(file_get_contents($composerFile), true);
    if (isset($composerData['advisories']) && !empty($composerData['advisories'])) {
        foreach ($composerData['advisories'] as $package => $advisory) {
            $riskSummary['HIGH']++;
            
            $findings[] = [
                'title' => "Vulnerable dependency: $package",
                'severity' => 'HIGH',
                'description' => $advisory['title'] ?? 'Known security vulnerability in dependency',
                'file' => 'composer.lock',
                'iso_control' => 'A.12.6.1 - Management of technical vulnerabilities',
                'cvss' => $advisory['cvss'] ?? '7.0',
                'category' => 'Dependency Vulnerability'
            ];
        }
    }
}

// Parse ZAP results if available
$zapFile = $reportsDir . '/zap-baseline.json';
if (file_exists($zapFile)) {
    $zapData = json_decode(file_get_contents($zapFile), true);
    // ZAP parsing would be more complex, simplified for demo
    if (isset($zapData['site'])) {
        foreach ($zapData['site'] as $site) {
            if (isset($site['alerts'])) {
                foreach ($site['alerts'] as $alert) {
                    $severity = mapZapRisk($alert['riskdesc']);
                    $riskSummary[$severity]++;
                    
                    $findings[] = [
                        'title' => $alert['name'],
                        'severity' => $severity,
                        'description' => $alert['desc'],
                        'file' => $alert['uri'] ?? 'Dynamic scan',
                        'iso_control' => mapZapToIso($alert['name']),
                        'cvss' => $alert['cweid'] ? '6.0' : '4.0',
                        'category' => 'Dynamic Analysis'
                    ];
                }
            }
        }
    }
}

// Additional manual findings based on code analysis
$manualFindings = analyzeCodePatterns($reportsDir);
foreach ($manualFindings as $finding) {
    $riskSummary[$finding['severity']]++;
    $findings[] = $finding;
}

// Generate the report
generateMarkdownReport($reportFile, $findings, $riskSummary, $iso27001Controls);

echo "Report generated: $reportFile\n";
echo "Total findings: " . count($findings) . "\n";
echo "High: {$riskSummary['HIGH']}, Medium: {$riskSummary['MEDIUM']}, Low: {$riskSummary['LOW']}\n";

function mapOwaspToIso($checkId) {
    $mapping = [
        'sql-injection' => 'A.14.2.1 - Secure development policy',
        'xss' => 'A.14.2.1 - Secure development policy',
        'csrf' => 'A.14.1.3 - Protection of application services transactions',
        'auth' => 'A.9.2.3 - Management of privileged access rights'
    ];
    
    foreach ($mapping as $pattern => $control) {
        if (strpos(strtolower($checkId), $pattern) !== false) {
            return $control;
        }
    }
    
    return 'A.14.2.1 - Secure development policy';
}

function calculateCvss($severity) {
    switch (strtoupper($severity)) {
        case 'HIGH': return '7.5';
        case 'MEDIUM': return '5.0';
        case 'LOW': return '2.5';
        default: return '0.0';
    }
}

function mapZapRisk($riskdesc) {
    if (strpos(strtolower($riskdesc), 'high') !== false) return 'HIGH';
    if (strpos(strtolower($riskdesc), 'medium') !== false) return 'MEDIUM';
    return 'LOW';
}

function mapZapToIso($alertName) {
    $mapping = [
        'Cross Site Scripting' => 'A.14.2.1 - Secure development policy',
        'SQL Injection' => 'A.14.2.1 - Secure development policy',
        'Missing Anti-CSRF Tokens' => 'A.14.1.3 - Protection of application services transactions'
    ];
    
    return $mapping[$alertName] ?? 'A.14.2.1 - Secure development policy';
}

function analyzeCodePatterns($reportsDir) {
    $findings = [];
    
    // Check config-check results
    $configFile = $reportsDir . '/config-check.txt';
    if (file_exists($configFile)) {
        $configContent = file_get_contents($configFile);
        if (strpos($configContent, 'DEBUG MODE ENABLED') !== false) {
            $findings[] = [
                'title' => 'Debug Mode Enabled in Production',
                'severity' => 'HIGH',
                'description' => 'Debug mode exposes sensitive information and stack traces',
                'file' => 'Configuration',
                'iso_control' => 'A.12.4.1 - Event logging',
                'cvss' => '5.3',
                'category' => 'Configuration'
            ];
        }
    }
    
    // Check CSRF protection
    $csrfFile = $reportsDir . '/csrf-check.txt';
    if (file_exists($csrfFile)) {
        $csrfContent = file_get_contents($csrfFile);
        if (strpos($csrfContent, 'No CSRF protection found') !== false) {
            $findings[] = [
                'title' => 'Missing CSRF Protection',
                'severity' => 'HIGH',
                'description' => 'Forms and state-changing operations lack CSRF protection',
                'file' => 'Application-wide',
                'iso_control' => 'A.14.1.3 - Protection of application services transactions',
                'cvss' => '8.1',
                'category' => 'Web Security'
            ];
        }
    }
    
    return $findings;
}

function generateMarkdownReport($reportFile, $findings, $riskSummary, $iso27001Controls) {
    $report = "# Penetration Test Rapport - BF Questionnaire Base\n\n";
    $report .= "**Datum:** " . date('Y-m-d H:i:s') . "\n";
    $report .= "**Tester:** Security Engineer (Automated ISO 27001 Pentest)\n";
    $report .= "**Scope:** PHP Laravel Questionnaire Application\n";
    $report .= "**Framework:** ISO/IEC 27001:2022 Annex A Controls + OWASP ASVS\n\n";
    
    // Executive Summary
    $report .= "## Executive Summary\n\n";
    $totalFindings = count($findings);
    $report .= "Deze penetratietes is uitgevoerd volgens ISO 27001-richtlijnen en OWASP-standaarden.\n\n";
    $report .= "**Totaal aantal bevindingen:** $totalFindings\n";
    $report .= "- 🔴 **Hoog risico:** {$riskSummary['HIGH']}\n";
    $report .= "- 🟡 **Gemiddeld risico:** {$riskSummary['MEDIUM']}\n";
    $report .= "- 🟢 **Laag risico:** {$riskSummary['LOW']}\n\n";
    
    // Risk assessment
    $overallRisk = 'LOW';
    if ($riskSummary['HIGH'] > 0) $overallRisk = 'HIGH';
    elseif ($riskSummary['MEDIUM'] > 2) $overallRisk = 'MEDIUM';
    
    $report .= "**Totaal risico-assessment:** $overallRisk\n\n";
    
    // Detailed findings
    $report .= "## Bevindingen\n\n";
    
    if (empty($findings)) {
        $report .= "✅ Geen kritieke bevindingen gevonden.\n\n";
    } else {
        // Group by severity
        $grouped = [];
        foreach ($findings as $finding) {
            $grouped[$finding['severity']][] = $finding;
        }
        
        foreach (['HIGH', 'MEDIUM', 'LOW'] as $severity) {
            if (!isset($grouped[$severity])) continue;
            
            $icon = $severity === 'HIGH' ? '🔴' : ($severity === 'MEDIUM' ? '🟡' : '🟢');
            $report .= "### $icon $severity Risk Bevindingen\n\n";
            
            foreach ($grouped[$severity] as $i => $finding) {
                $num = $i + 1;
                $report .= "#### $severity-$num: {$finding['title']}\n\n";
                $report .= "**Ernst:** {$finding['severity']}\n\n";
                $report .= "**Beschrijving:** {$finding['description']}\n\n";
                $report .= "**Locatie:** `{$finding['file']}`\n\n";
                $report .= "**ISO 27001 Control:** {$finding['iso_control']}\n\n";
                $report .= "**CVSS v3.1 Score:** {$finding['cvss']}\n\n";
                $report .= "**Categorie:** {$finding['category']}\n\n";
                
                // Add mitigation recommendations
                $report .= "**Aanbevolen mitigatie:**\n";
                $report .= getMitigationRecommendation($finding) . "\n\n";
                
                $report .= "---\n\n";
            }
        }
    }
    
    // ISO 27001 Controls Summary
    $report .= "## ISO 27001 Annex A Controls Mapping\n\n";
    $report .= "| Control | Beschrijving | Bevindingen |\n";
    $report .= "|---------|--------------|-------------|\n";
    
    foreach ($iso27001Controls as $control => $description) {
        $count = 0;
        foreach ($findings as $finding) {
            if (strpos($finding['iso_control'], $control) !== false) {
                $count++;
            }
        }
        $report .= "| $control | $description | $count |\n";
    }
    
    $report .= "\n";
    
    // Recommendations
    $report .= "## Aanbevelingen\n\n";
    $report .= "### Prioriteit 1 (Onmiddellijk)\n";
    $report .= "- Herstel alle HIGH risk bevindingen\n";
    $report .= "- Implementeer security headers (CSP, HSTS, X-Frame-Options)\n";
    $report .= "- Valideer alle input en output encoding\n\n";
    
    $report .= "### Prioriteit 2 (Binnen 30 dagen)\n";
    $report .= "- Herstel MEDIUM risk bevindingen\n";
    $report .= "- Implementeer security monitoring en logging\n";
    $report .= "- Update alle dependencies naar latest versies\n\n";
    
    $report .= "### Prioriteit 3 (Binnen 90 dagen)\n";
    $report .= "- Herstel LOW risk bevindingen\n";
    $report .= "- Implementeer automated security testing in CI/CD\n";
    $report .= "- Voer regular security assessments uit\n\n";
    
    // Retest planning
    $report .= "## Retest Planning\n\n";
    $report .= "**Retest datum:** " . date('Y-m-d', strtotime('+30 days')) . "\n";
    $report .= "**Scope:** Verificatie van geïmplementeerde mitigaties\n";
    $report .= "**Acceptatiecriteria:** Alle HIGH en MEDIUM risks opgelost\n\n";
    
    $report .= "---\n";
    $report .= "*Dit rapport is gegenereerd conform ISO/IEC 27001:2022 Annex A controls en OWASP ASVS v4.0*\n";
    
    file_put_contents($reportFile, $report);
}

function getMitigationRecommendation($finding) {
    $recommendations = [
        'Debug Mode Enabled' => '- Zet APP_DEBUG=false in productie omgeving\n- Implementeer proper error handling zonder stack traces\n- Configureer logging voor development debugging',
        'Missing CSRF Protection' => '- Implementeer Laravel CSRF middleware op alle forms\n- Gebruik @csrf directive in Blade templates\n- Valideer CSRF tokens op alle POST/PUT/DELETE requests',
        'SQL Injection' => '- Gebruik altijd prepared statements of Eloquent ORM\n- Valideer en sanitize alle user input\n- Implementeer SQL injection protection middleware',
        'Cross Site Scripting' => '- Gebruik Laravel\'s automatic escaping in Blade\n- Implementeer Content Security Policy headers\n- Valideer en encode alle user-generated content',
        'Vulnerable dependency' => '- Update dependency naar latest veilige versie\n- Monitor security advisories regular\n- Implementeer automated dependency scanning'
    ];
    
    foreach ($recommendations as $pattern => $recommendation) {
        if (strpos($finding['title'], $pattern) !== false) {
            return $recommendation;
        }
    }
    
    return "- Review en implementeer security best practices\n- Volg OWASP guidelines voor deze vulnerability\n- Implementeer proper input validation en output encoding";
}

?>