---
name: Sovereign Rural Ledger
colors:
  surface: '#faf8ff'
  surface-dim: '#d2d9f4'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3ff'
  surface-container: '#eaedff'
  surface-container-high: '#e2e7ff'
  surface-container-highest: '#dae2fd'
  on-surface: '#131b2e'
  on-surface-variant: '#5b403d'
  inverse-surface: '#283044'
  inverse-on-surface: '#eef0ff'
  outline: '#8f6f6c'
  outline-variant: '#e4beb9'
  surface-tint: '#b91c1c'
  primary: '#93000b'
  on-primary: '#ffffff'
  primary-container: '#b91c1c'
  on-primary-container: '#ffcdc7'
  inverse-primary: '#ffb4ab'
  secondary: '#006d30'
  on-secondary: '#ffffff'
  secondary-container: '#92f5a4'
  on-secondary-container: '#007233'
  tertiary: '#614000'
  on-tertiary: '#ffffff'
  tertiary-container: '#805600'
  on-tertiary-container: '#ffd18f'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad6'
  primary-fixed-dim: '#ffb4ab'
  on-primary-fixed: '#410002'
  on-primary-fixed-variant: '#93000b'
  secondary-fixed: '#95f8a7'
  secondary-fixed-dim: '#79db8d'
  on-secondary-fixed: '#00210a'
  on-secondary-fixed-variant: '#005323'
  tertiary-fixed: '#ffddb0'
  tertiary-fixed-dim: '#ffba46'
  on-tertiary-fixed: '#281800'
  on-tertiary-fixed-variant: '#614000'
  background: '#faf8ff'
  on-background: '#131b2e'
  surface-variant: '#dae2fd'
typography:
  headline-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-xl-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.015em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 26px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: '0'
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: '0'
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
    letterSpacing: 0.01em
  tabular-mono:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
    letterSpacing: -0.01em
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-tablet: 1.25rem
  gutter-desktop: 1.5rem
  margin: 1rem
  margin-tablet: 1.5rem
  margin-desktop: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 0.75rem
  space-lg: 1.25rem
  space-xl: 2rem
---

## Brand & Style

### Brand Personality & Core Audience
This design system serves village-owned enterprise management (BUMDes Kuala Alam), field accountants, and regional audit stakeholders. The brand personality embodies institutional reliability, fiscal rigor, and public accountability. The emotional response sought is authoritative trust, operational precision, and absolute transparency in compliance with Indonesian SAK ETAP accounting standards.

### Design Movement: Modern Corporate Accounting with Semantic High-Contrast Accents
The interface rejects decorative skeuomorphism in favor of a crisp, institutional corporate UI. It leverages precise tabular densities, clean grid alignments, and a clear functional semantic hierarchy. While the structural canvas is anchored in deep financial slate and high-legibility crisp neutrals, the iconic triad of the BUMDes emblem (Crimson Red, Civic Gold, and Enterprise Green) is repurposed strictly for state semantics, validation flags, double-entry balances, and high-impact transaction controls.

## Colors

### Palette Strategy & Semantic Mapping
The color architecture adapts the heraldic BUMDes identity into enterprise accounting states:

- **Primary (`#B91C1C` / `#DC2626` Crimson):** Primary administrative actions, high-level fiscal liability markers, expense indicators, and critical reconciliation alerts.
- **Secondary (`#15803D` / `#16A34A` Emerald):** Revenue lines, asset indicators, positive ledger entries, balanced-status indicators, and official sign-off confirmations.
- **Tertiary (`#CA8A04` / `#EAB308` Gold/Amber):** Pending ledger approvals, escrow/holding balances, supervisory audit notes, and draft SAK ETAP reporting milestones.
- **Neutral Foundation (`#0F172A` Dark Slate to `#F8FAFC` Slate Canvas):** High-density tabular backgrounds, crisp borders, and deep charcoal text elements that ensure all-day comfort for data entry.

### Surface System
- `canvas-default`: `#F8FAFC`
- `surface-elevated`: `#FFFFFF`
- `surface-muted`: `#F1F5F9`
- `border-hairline`: `#E2E8F0`
- `border-strong`: `#CBD5E1`
- `text-primary`: `#0F172A`
- `text-secondary`: `#475569`
- `text-muted`: `#94A3B8`

## Typography

### Structural Decisions
The typography pairs **Plus Jakarta Sans** for structural headers, summary aggregates, and dashboard KPIs with **Inter** for dense transactional interfaces, forms, and general ledger reports. 

### Tabular Formatting
All financial numbers, currency representations (IDR/Rp), and ledger entries must enforce tabular numerical alignment (`font-feature-settings: "tnum" 1, "cv05" 1`). This guarantees that debit and credit columns line up pixel-perfect down the decimal separator, eliminating visual scanning fatigue during multi-row journal reconciliation.

## Layout & Spacing

### Layout Model
The layout adheres to a 12-column adaptive grid on desktop (`1200px` to `1600px` canvas max), dropping to an 8-column model on tablet (`768px` to `1024px`) and a 4-column stacked model on mobile. 

A high-density operational grid is sustained inside table panels and multi-input journal rows, utilizing `0.75rem` (`space-md`) vertical rhythm to maximize information visibility without feeling congested. Outer page structures employ generous `2rem` (`space-xl`) breaks to demarcate analytical widgets, chart metrics, and official audit breadcrumbs.

## Elevation & Depth

Visual hierarchy uses architectural, low-contrast structural elevation rather than fuzzy, distracting dropshadows:

1. **Level 0 (Flat Canvas):** `#F8FAFC` base page background.
2. **Level 1 (Card & Module Layer):** `#FFFFFF` surface bordered by a crisp `1px solid #E2E8F0` hairline outline, backed by an ambient feather shadow: `0px 1px 3px rgba(15, 23, 42, 0.05)`.
3. **Level 2 (Active Drawer & Table Filter Overlay):** `#FFFFFF` surface with `0px 4px 12px rgba(15, 23, 42, 0.08)` and `1px solid #CBD5E1`.
4. **Level 3 (Modal Vouchers & Verification Dialogues):** Deep backdrop blur (`backdrop-filter: blur(4px)` with `#0F172A` at 40% opacity) beneath a focused modal container structured with `0px 12px 32px rgba(15, 23, 42, 0.16)`.

## Shapes

### Strict Corporate Form Factor
This design system enforces a disciplined `roundedness: 1` (`0.25rem` / `4px` default radius). 
- Form inputs, buttons, table cell selections, and transaction row highlights use `4px` corner radii.
- Executive metric cards, modal shells, and floating panels scale to `8px` (`rounded-lg`).
- Status badges and verification pills retain a compact, micro-radius (`3px` to `4px`) rather than full pill circularity, preserving the utilitarian, formal banking appearance.

## Components

### Buttons
- **Primary Action (Confirm/Post Journal):** Solid Red `#B91C1C`, white text, `4px` radius, hover `#991B1B`, active `#7F1D1D`. Strict focus ring: `2px solid #B91C1C` with `2px` offset.
- **Secondary Action (Export SAK ETAP / Filter):** Surface `#FFFFFF`, border `1px solid #CBD5E1`, text `#0F172A`, hover `#F1F5F9`.
- **Success/Approval Action:** Surface `#15803D`, white text, hover `#166534`.
- **Destructive/Void Action:** Muted red wash `#FEF2F2`, border `1px solid #FECACA`, text `#B91C1C`.

### Verification Badges & Audit Chips
- **Status "Verified / Sesuai SAK ETAP":** Light green background (`#DCFCE7`), deep green text (`#166534`), left border indicator `2px solid #15803D`.
- **Status "Pending Review / Draft":** Amber wash (`#FEF9C3`), dark amber text (`#854D0E`), border `1px solid #FDE047`.
- **Status "Unbalanced / Selisih":** Crisp red wash (`#FEE2E2`), red text (`#991B1B`), border `1px solid #F87171`.

### Double-Entry Accounting Tables
- **Header:** Sticky top, background `#F1F5F9`, border-bottom `2px solid #CBD5E1`, typography `label-sm` uppercase tracking `#475569`.
- **Data Rows:** Zebra striping omitted in favor of clean hairline borders (`1px solid #E2E8F0`). Hover state triggers `#F8FAFC`.
- **Alignment Rules:** Text left-aligned; account codes center-aligned; debit, credit, and running balance right-aligned in `tabular-mono`.
- **Summary Footer:** Background `#F8FAFC`, top border `2px solid #0F172A`, bottom border double-line (`3px double #0F172A`) per standard accounting convention.

### Executive Metric Cards
- White background (`#FFFFFF`), `1px solid #E2E8F0` border, `8px` corner radius.
- Includes a subtle vertical accent bar on the left edge (`3px width` using semantic Gold `#CA8A04` for Liquidity, Green `#15803D` for Net Surplus/SHU, Red `#B91C1C` for Payables/Liabilities).
- Primary metrics styled in `headline-lg` with tabular numbers accompanied by subtle period-over-period delta badges.

### Form Inputs & Voucher Editors
- Height: `36px` compact default, background `#FFFFFF`, border `1px solid #CBD5E1`, border-radius `4px`.
- Active focus state: border `#0F172A` with a subtle focus shadow. Error state: border `#DC2626` with descriptive inline microcopy.