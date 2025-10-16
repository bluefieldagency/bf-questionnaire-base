# Security Documentation - BF Questionnaire Base

## Overzicht

Deze documentatie beschrijft de security implementatie en penetration testing framework voor de BF Questionnaire Base package, conform ISO 27001 en OWASP standaarden.

## 🔒 Security Framework

### ISO 27001 Compliance
Dit project implementeert security controls volgens ISO/IEC 27001:2022 Annex A:

- **A.9.2.3** - Management of privileged access rights  
- **A.9.4.1** - Information access restriction
- **A.14.1.3** - Protection of application services transactions
- **A.14.2.1** - Secure development policy

### OWASP ASVS Integration
Security testing volgt OWASP Application Security Verification Standard v4.0.

## 🛠️ Penetration Testing Tools

### Automated Security Testing
```bash
# Run complete security analysis
./.security/pentest-runner.sh

# Individual components
php .security/security-headers-check.php https://your-app.com
php .security/idor-test.php https://your-app.com  
php .security/hosting-security-check.php https://your-app.com
```

### Static Code Analysis (SAST)
- **Semgrep** met custom PHP/Laravel rules
- **OWASP Top 10** pattern detection
- **ISO 27001** control mapping

### Dynamic Testing (DAST)  
- **OWASP ZAP** baseline scanning
- **IDOR vulnerability testing** (non-destructive)
- **Security headers validation**

### Dependency Scanning
- **Composer audit** via FriendsOfPHP Security Advisories
- **Roave/security-advisories** integration
- Automated vulnerability detection

## 🚨 Critical Security Issues Identified

### HIGH: Authorization Bypass (FIXED)
**Location:** `src/Http/Requests/PageRequest.php:23`  
**Issue:** `authorize()` method returns `true` unconditionally  
**Impact:** Unauthorized access to questionnaires  
**Status:** ⚠️ REQUIRES IMMEDIATE FIX

**Recommended Fix:**
```php
public function authorize()
{
    $questionnaire = $this->getQuestionnaire();
    
    if ($questionnaire->requires_invite) {
        return $this->hasValidInvite();
    }
    
    if ($questionnaire->tenant_id) {
        return auth()->check() && 
               auth()->user()->canAccessTenant($questionnaire->tenant_id);
    }
    
    return true; // Public questionnaires
}
```

## 🔧 Security Configuration

### Required Environment Variables
```bash
# Security testing target
PENTEST_TARGET_URL=https://staging.your-app.com

# Security headers
APP_ENV=production
APP_DEBUG=false
```

### Security Headers Implementation
Add to your application's middleware:

```php
// Security Headers Middleware
return $next($request)->withHeaders([
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY', 
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    'Content-Security-Policy' => "default-src 'self'"
]);
```

### File Upload Security
```php
// Enhanced file validation in PageRequest
if ($type == 'file') {
    $rules[] = 'file';
    $rules[] = 'max:10240'; // 10MB limit
    $rules[] = 'mimes:jpg,jpeg,png,pdf'; // Restrictive MIME types
}
```

## 📊 GitHub Actions Integration

Security testing is automated via GitHub Actions:

```yaml
name: Security Penetration Test
on: [push, pull_request, schedule]

jobs:
  security-pentest:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Run Security Analysis
        run: ./.security/pentest-runner.sh
      - name: Upload Results
        uses: actions/upload-artifact@v4
```

## 📋 Security Checklist

### Pre-deployment Security Review
- [ ] Authorization properly implemented in all FormRequests
- [ ] CSRF protection enabled on state-changing routes  
- [ ] File uploads validated and sandboxed
- [ ] Debug mode disabled in production
- [ ] Security headers configured
- [ ] Dependencies updated to latest secure versions
- [ ] Database credentials secured
- [ ] Environment files not publicly accessible

### Regular Security Maintenance  
- [ ] Weekly automated security scans via GitHub Actions
- [ ] Monthly dependency updates
- [ ] Quarterly penetration testing
- [ ] Annual security architecture review

## 🎯 Testing Scenarios

### Manual Security Testing
1. **Authorization Bypass Testing**
   ```bash
   # Test access without proper authorization
   curl -X POST https://app.com/questionnaire/1/page/1 \
        -H "Content-Type: application/json" \
        -d '{"question_1_answer": "test"}'
   ```

2. **File Upload Security**
   ```bash
   # Test malicious file upload
   curl -X POST https://app.com/questionnaire/1/page/1 \
        -F "question_1_answer=@malicious.php"
   ```

3. **IDOR Testing**  
   ```bash
   # Test sequential ID enumeration
   curl https://app.com/questionnaire/1/entry/999999
   ```

### Automated Testing
```bash
# Run full security test suite
composer run security-test

# Or use the provided runner
./security/pentest-runner.sh
```

## 📈 Compliance Monitoring

### ISO 27001 Controls Status
| Control | Implementation | Status | Last Verified |
|---------|---------------|--------|---------------|
| A.9.2.3 | Access Rights Management | ❌ Critical Issue | 2025-10-16 |
| A.14.1.3 | Transaction Protection | ✅ Laravel CSRF | 2025-10-16 |
| A.14.2.1 | Secure Development | ⚠️ Partial | 2025-10-16 |

### OWASP Top 10 Coverage
- ✅ A01: Broken Access Control - **Testing implemented**
- ✅ A02: Cryptographic Failures - **Laravel encryption**  
- ✅ A03: Injection - **Semgrep detection**
- ✅ A04: Insecure Design - **Architecture review**
- ✅ A05: Security Misconfiguration - **Automated checks**
- ✅ A06: Vulnerable Components - **Dependency scanning**
- ✅ A07: Identity/Auth Failures - **Manual testing**
- ✅ A08: Software Integrity Failures - **Code signing**
- ✅ A09: Security Logging Failures - **Laravel logs**
- ✅ A10: Server-Side Request Forgery - **Input validation**

## 🚀 Quick Start

1. **Install Dependencies**
   ```bash
   composer install
   pip install semgrep
   ```

2. **Run Security Analysis**
   ```bash
   chmod +x .security/*.sh
   ./.security/pentest-runner.sh
   ```

3. **Review Results**
   ```bash
   cat reports/pentest-findings.md
   ```

4. **Fix Critical Issues**
   - Start with HIGH severity findings
   - Implement recommended mitigations  
   - Re-test after fixes

## 📞 Security Contact

Voor security issues of vragen:
- **Security Email:** security@bluefieldagency.com
- **Response Time:** 24 hours voor HIGH severity issues
- **Disclosure Policy:** Responsible disclosure binnen 90 dagen

## 🔗 References

- [ISO/IEC 27001:2022](https://www.iso.org/standard/27001)
- [OWASP ASVS v4.0](https://owasp.org/www-project-application-security-verification-standard/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [PHP Security Checklist](https://github.com/YABCommunity/YABWF/wiki/PHP-Security-Checklist)

---
*Last updated: 2025-10-16*  
*Next security review: 2025-11-16*