# Exam Portal - Future Updates & Roadmap

## Priority: High

### Database Persistence
- [ ] Switch from SQLite to PostgreSQL (Render free tier resets SQLite on redeploy)
- [ ] Use Render's free PostgreSQL or external service (Supabase/Neon)

### Exam System
- [ ] Create exam builder in admin panel (add questions, set time limits)
- [ ] Multiple question types: multiple choice, true/false, short answer, essay
- [ ] Exam page that students get redirected to from waiting room
- [ ] Timer countdown per exam with auto-submit
- [ ] Answer collection and storage
- [ ] Auto-grading for multiple choice / true/false
- [ ] Results page for students after submission

### Student Management
- [ ] Assign students to specific exams or groups
- [ ] Student login system (not just registration)
- [ ] Unique exam codes / access links per exam session
- [ ] Student photo upload for identity verification

## Priority: Medium

### Enhanced Monitoring
- [ ] Fullscreen enforcement during exam
- [ ] Detect developer tools opening (F12)
- [ ] Detect window resize (possible split-screen cheating)
- [ ] Screenshot capture at intervals (webcam)
- [ ] Keystroke logging for text answers
- [ ] Flag dashboard with severity levels and admin alerts

### Admin Features
- [ ] Bulk redirect all students at once
- [ ] Kick/ban student from exam
- [ ] Send message/notification to individual student
- [ ] Export student data and results to CSV/Excel
- [ ] Multiple admin accounts with roles
- [ ] Exam scheduling (start/end times)

### Communication
- [ ] Email notifications to students (registration confirmation, exam links)
- [ ] Admin email alerts for flagged events
- [ ] In-app chat between admin and student

## Priority: Low

### UI/UX Improvements
- [ ] Dark mode toggle
- [ ] Multi-language support (i18n)
- [ ] Mobile-responsive admin panel improvements
- [ ] Student progress bar during exam
- [ ] Custom branding (logo, colors, institution name)

### Security Enhancements
- [ ] CSRF token protection on all forms
- [ ] Rate limiting on API endpoints
- [ ] IP-based access control for admin panel
- [ ] Two-factor authentication for admin
- [ ] Encrypted session storage

### Analytics & Reports
- [ ] Exam results analytics (average scores, pass rates)
- [ ] Student performance over time
- [ ] Cheating attempt statistics per exam
- [ ] PDF report generation per student
- [ ] Dashboard charts and graphs
