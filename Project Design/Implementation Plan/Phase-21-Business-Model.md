# Phase 21: Business Model Features

## 21.1 Freemium Tier Implementation
**Effort: Medium**

Implement limited free tier.

**Freemium Limits:**
- 1 location
- 5 active employees
- Basic scheduling only
- No time & attendance (premium addon)
- No reports
- Plannrly branding

**Time & Attendance Feature:**
- Not available on Basic plan
- Available as $12/month addon on Professional plan
- Included in Enterprise plan

**Tasks:**
- [ ] Add 'free' plan to subscription system
- [ ] Implement location limit check
- [ ] Implement employee limit check
- [ ] Disable premium features for free tier
- [ ] Add upgrade prompts at limit points
- [ ] Add Plannrly branding to free tier emails
- [ ] Create upgrade path UI
- [ ] Write tests

---

## 21.2 Per-Employee Pricing
**Effort: Medium**

Usage-based pricing component.

**Tasks:**
- [ ] Track active employees per billing period
- [ ] "Active" = scheduled or clocked in during period
- [ ] Create monthly usage calculation
- [ ] Integrate with Stripe metered billing
- [ ] Show usage in billing dashboard
- [ ] Send usage reports to tenants
- [ ] Write tests
