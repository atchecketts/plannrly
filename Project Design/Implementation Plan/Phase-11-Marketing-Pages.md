# Phase 11: Marketing & Public Pages

## 11.1 Landing Page
**Effort: Large**

Public-facing homepage to convert visitors to customers.

**Files to create:**
- `resources/views/public/welcome.blade.php`
- `resources/views/public/components/hero.blade.php`
- `resources/views/public/components/features.blade.php`
- `resources/views/public/components/pricing-preview.blade.php`
- `resources/views/public/components/testimonials.blade.php`
- `resources/views/public/components/faq.blade.php`
- `resources/views/public/components/footer.blade.php`
- `resources/views/layouts/public.blade.php`

**Sections:**
| Section | Description |
|---------|-------------|
| Hero | Headline, subheading, CTA buttons, hero image |
| Features | Grid of key features with icons |
| How It Works | 3-4 step process explanation |
| Pricing Preview | Tier cards with "See Full Pricing" link |
| Testimonials | Customer quotes with photos/logos |
| FAQ | Expandable Q&A section |
| CTA Banner | Final call-to-action before footer |
| Footer | Links, social, legal |

**Tasks:**
- [ ] Create public layout (no auth required)
- [ ] Design and build hero section
- [ ] Create features grid component
- [ ] Create how-it-works section
- [ ] Create pricing preview cards
- [ ] Create testimonials carousel/grid
- [ ] Create FAQ accordion
- [ ] Create CTA banner
- [ ] Create footer with links
- [ ] Add animations/transitions
- [ ] Ensure mobile responsiveness
- [ ] Add SEO meta tags
- [ ] Write tests

---

## 11.2 Pricing Page
**Effort: Medium**

Detailed pricing with feature comparison.

**Files to create:**
- `resources/views/public/pricing.blade.php`
- `resources/views/public/components/pricing-table.blade.php`
- `resources/views/public/components/feature-comparison.blade.php`

**Features:**
- Monthly/annual toggle with savings shown
- Three main tiers (Starter, Professional, Enterprise)
- Feature comparison table
- Add-on pricing section
- FAQ specific to pricing
- Contact for enterprise custom pricing

**Tasks:**
- [ ] Create pricing page view
- [ ] Build pricing tier cards
- [ ] Create feature comparison table
- [ ] Add monthly/annual toggle
- [ ] Show annual savings percentage
- [ ] Create add-on pricing section
- [ ] Add enterprise inquiry form/CTA
- [ ] Ensure mobile responsiveness
- [ ] Write tests

---

## 11.3 Features Page
**Effort: Medium**

In-depth feature showcase.

**Files to create:**
- `resources/views/public/features.blade.php`
- `resources/views/public/features/scheduling.blade.php`
- `resources/views/public/features/time-attendance.blade.php`
- `resources/views/public/features/employee-management.blade.php`

**Tasks:**
- [ ] Create main features overview page
- [ ] Create detailed page per feature category
- [ ] Add screenshots/mockups
- [ ] Create feature comparison with competitors (optional)
- [ ] Add use case examples
- [ ] Write tests

---

## 11.4 Legal & Support Pages
**Effort: Medium**

Required legal and support pages.

**Files to create:**
- `resources/views/public/about.blade.php`
- `resources/views/public/contact.blade.php`
- `resources/views/public/terms.blade.php`
- `resources/views/public/privacy.blade.php`
- `resources/views/public/cookies.blade.php`
- `app/Http/Controllers/ContactController.php`

**Tasks:**
- [ ] Create About Us page
- [ ] Create Contact page with form
- [ ] Create Terms of Service page
- [ ] Create Privacy Policy page
- [ ] Create Cookie Policy page
- [ ] Implement contact form submission
- [ ] Add email notification for contact form
- [ ] Ensure GDPR compliance notices
- [ ] Write tests
