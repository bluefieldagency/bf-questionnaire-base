# Security Testing Usage Guide

## Quick Start

### 1. Run Complete Security Analysis
```bash
# Make scripts executable (one time setup)
chmod +x .security/*.sh .security/*.php

# Run complete penetration test
./.security/pentest-runner.sh

# View results
cat reports/pentest-findings.md
```

### 2. Individual Security Tests

#### Static Code Analysis
```bash
# Install Semgrep
pip install semgrep

# Run custom PHP/Laravel security rules
semgrep --config=.security/semgrep.yml src/

# Run OWASP Top 10 rules
semgrep --config=p/owasp-top-ten src/
```

#### Security Headers Check
```bash
# Test security headers for a target URL
php .security/security-headers-check.php https://your-app.com

# Save results to file
php .security/security-headers-check.php https://your-app.com reports/headers.json
```

#### IDOR Vulnerability Testing
```bash
# Non-destructive IDOR testing
php .security/idor-test.php https://your-app.com

# With results output
php .security/idor-test.php https://your-app.com reports/idor.json
```

#### Hosting Security Check
```bash
# Check hosting configuration security
php .security/hosting-security-check.php https://your-app.com

# Full report
php .security/hosting-security-check.php https://your-app.com reports/hosting.json
```

### 3. GitHub Actions Integration

The security tests automatically run on:
- Every push to main/master/develop
- Every pull request
- Weekly schedule (Mondays at 2 AM UTC)
- Manual workflow dispatch

#### Set Required Secrets
```bash
# In your GitHub repository settings, add:
PENTEST_TARGET_URL=https://staging.your-app.com
```

#### Manual Workflow Trigger
1. Go to Actions tab in your GitHub repository
2. Select "Security Penetration Test" workflow
3. Click "Run workflow"
4. Enter target URL (optional)
5. Download security report from artifacts

## Critical Security Fix Required

### 🚨 Authorization Bypass (HIGH SEVERITY)

**File:** `src/Http/Requests/PageRequest.php` line 23  
**Issue:** Authorization always returns `true`

**Current Code:**
```php
public function authorize()
{
    return true; // ❌ SECURITY ISSUE
}
```

**Required Fix:**
```php
public function authorize()
{
    $parameters = request()->route()->parameters();
    
    // Get questionnaire from route parameters  
    $questionnaire = null;
    if (isset($parameters['questionnaire_slug'])) {
        $questionnaire = $this->getQuestionnaire($parameters['questionnaire_slug']);
    } elseif (isset($parameters['questionnaire'])) {
        $questionnaire = $this->getQuestionnaire($parameters['questionnaire']);
    }
    
    if (!$questionnaire) {
        return false;
    }
    
    // Check if questionnaire requires invite
    if ($questionnaire->requires_invite) {
        return $this->hasValidInviteHash();
    }
    
    // Check tenant access if applicable
    if ($questionnaire->tenant_id) {
        return auth()->check() && 
               auth()->user()->canAccessTenant($questionnaire->tenant_id);
    }
    
    // Allow access to public questionnaires
    return !$questionnaire->requires_invite;
}

private function hasValidInviteHash()
{
    $hash = request()->get('invite') ?? session('questionnaire_invite_hash');
    
    if (!$hash) {
        return false;
    }
    
    return \Questionnaire\Models\QuestionnaireInvite::where('hash', $hash)
                                                   ->where('expires_at', '>', now())
                                                   ->exists();
}
```

### Additional Security Hardening

#### 1. Implement Security Middleware
Create `app/Http/Middleware/SecurityHeaders.php`:
```php
<?php

namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        if (request()->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        // Basic CSP (customize as needed)
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'";
        $response->headers->set('Content-Security-Policy', $csp);
        
        return $response;
    }
}
```

Register in `app/Http/Kernel.php`:
```php
protected $middleware = [
    // ... other middleware
    \App\Http\Middleware\SecurityHeaders::class,
];
```

#### 2. Enhanced File Validation
In `PageRequest.php`:
```php
protected function forQuestion($question)
{
    $type = $question->question_type->type;
    $ruleClass = 'Questionnaire\\Rules\\' . ucfirst($type) . 'Rule';
    $rules = [new $ruleClass($this->page, $question)];

    if ($question->is_required) {
        $rules[] = 'required';
    }

    if ($type == 'file') {
        $rules[] = 'file';
        $rules[] = 'max:10240'; // 10MB limit
        $rules[] = 'mimes:jpg,jpeg,png,pdf,doc,docx'; // Restricted types
        
        // Additional security validation
        $rules[] = function ($attribute, $value, $fail) {
            // Check file content matches extension
            if ($value && $value->isValid()) {
                $mimeType = $value->getMimeType();
                $extension = $value->getClientOriginalExtension();
                
                // Basic MIME/extension validation
                $allowedMimes = [
                    'jpg' => ['image/jpeg'],
                    'jpeg' => ['image/jpeg'], 
                    'png' => ['image/png'],
                    'pdf' => ['application/pdf'],
                    'doc' => ['application/msword'],
                    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']
                ];
                
                if (isset($allowedMimes[$extension]) && 
                    !in_array($mimeType, $allowedMimes[$extension])) {
                    $fail('File content does not match extension.');
                }
            }
        };
    }

    return $rules;
}
```

## Continuous Security Monitoring

### Weekly Security Tasks
1. Review security scan results in GitHub Actions
2. Update dependencies: `composer update`
3. Check for new security advisories
4. Review access logs for suspicious activity

### Monthly Security Tasks  
1. Run full penetration test: `./.security/pentest-runner.sh`
2. Review and update security configurations
3. Update security documentation
4. Security team briefing on findings

### Quarterly Security Tasks
1. External security audit
2. Security architecture review
3. Incident response plan testing
4. Security training for development team

## Compliance Checklist

### Before Production Deployment
- [ ] Authorization bypass fixed in PageRequest.php
- [ ] Security headers middleware implemented
- [ ] File upload validation enhanced
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Environment files secured (not web accessible)
- [ ] Database credentials secured
- [ ] All dependencies updated to latest secure versions
- [ ] CSRF protection verified on all forms
- [ ] HTTPS enforced
- [ ] Security monitoring configured

### ISO 27001 Compliance Verification
- [ ] A.9.2.3 - Access rights properly managed
- [ ] A.14.1.3 - CSRF protection implemented  
- [ ] A.14.2.1 - Input validation comprehensive
- [ ] A.12.6.1 - Vulnerability management active
- [ ] A.13.1.1 - Network security configured

## Emergency Security Response

### High Severity Issue Found
1. **Immediate:** Block access if actively exploited
2. **Within 4 hours:** Implement hotfix
3. **Within 24 hours:** Deploy fix to production
4. **Within 48 hours:** Verify fix effectiveness
5. **Within 1 week:** Conduct security review

### Contact Information
- **Security Team:** security@bluefieldagency.com
- **Response Time:** 4 hours for HIGH, 24 hours for MEDIUM
- **Emergency:** Use GitHub security advisories for critical issues

## References

- [ISO 27001 Controls](https://www.iso.org/standard/27001)
- [OWASP ASVS](https://owasp.org/www-project-application-security-verification-standard/)
- [Laravel Security](https://laravel.com/docs/security)
- [PHP Security Checklist](https://github.com/YABCommunity/YABWF/wiki/PHP-Security-Checklist)