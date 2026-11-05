import io, json, os, sys
from pathlib import Path
from xml.sax.saxutils import escape
from reportlab.pdfgen import canvas
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, KeepTogether
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from pypdf import PdfReader

root=Path(__file__).resolve().parent.parent
manifest=json.loads(Path(sys.argv[1]).read_text(encoding='utf-8'))
folder=root/'uploads'/'201_files'
folder.mkdir(parents=True,exist_ok=True)
pdfmetrics.registerFont(TTFont('Body','C:/Windows/Fonts/arial.ttf'))
pdfmetrics.registerFont(TTFont('BodyBold','C:/Windows/Fonts/arialbd.ttf'))
styles=getSampleStyleSheet()
styles.add(ParagraphStyle(name='TitleLocal',fontName='BodyBold',fontSize=19,leading=24,textColor=colors.HexColor('#102544'),spaceAfter=12))
styles.add(ParagraphStyle(name='BodyLocal',fontName='Body',fontSize=10,leading=15,textColor=colors.HexColor('#26364A'),spaceAfter=8))
styles.add(ParagraphStyle(name='HeadingLocal',fontName='BodyBold',fontSize=11,leading=16,textColor=colors.HexColor('#102544'),spaceBefore=12,spaceAfter=7))
styles.add(ParagraphStyle(name='SmallLocal',fontName='Body',fontSize=9,leading=13,textColor=colors.HexColor('#475569')))

def p(text,style='BodyLocal'):
    return Paragraph(escape(str(text)).replace('\n','<br/>'),styles[style])
def table(rows,widths):
    t=Table([[p(x,'SmallLocal') for x in row] for row in rows],colWidths=widths,hAlign='LEFT')
    t.setStyle(TableStyle([('VALIGN',(0,0),(-1,-1),'TOP'),('BACKGROUND',(0,0),(-1,0),colors.HexColor('#EDF3FA')),('LINEBELOW',(0,0),(-1,0),0.7,colors.HexColor('#CCD7E5')),('LINEBELOW',(0,1),(-1,-1),0.3,colors.HexColor('#DFE6EF')),('LEFTPADDING',(0,0),(-1,-1),9),('RIGHTPADDING',(0,0),(-1,-1),9),('TOPPADDING',(0,0),(-1,-1),8),('BOTTOMPADDING',(0,0),(-1,-1),8)]))
    return t

def footer(c,doc):
    c.saveState();c.setStrokeColor(colors.HexColor('#CCD7E5'));c.line(42,39,A4[0]-42,39)
    c.setFont('Body',8);c.setFillColor(colors.HexColor('#64748B'));c.drawString(42,26,'PAGECOM HRIS | Personnel Records');c.drawRightString(A4[0]-42,26,f'Page {doc.page}');c.restoreState()

roles={'employee':'Employee','hr':'Human Resource','admin':'IT Administrator'}
responsibilities={
 'employee':['Complete assigned operational work and submit supporting evidence.','Maintain accurate attendance and communicate availability to the reviewer.','Follow work instructions, protect records and report unresolved issues.','Submit completed assignments by their deadline and act on feedback.'],
 'hr':['Maintain personnel files and review staff document submissions.','Reconcile attendance, manage assigned leave reviews and validate payroll inputs.','Assign and review staff evaluations with evidence-based scores and feedback.','Protect personnel information and escalate incomplete records.'],
 'admin':['Manage user accounts, departments and authorized system access.','Review password-reset tickets and resolve technical support issues.','Monitor system records and verify backups and service availability.','Document changes, follow security controls and escalate incidents.']}
created=0;pages=0
for record in manifest['records']:
    target=folder/record['file_name']
    if target.exists():
        reader=PdfReader(target)
        if not reader.pages:raise ValueError('Existing PDF has no pages')
        continue
    u=record['person'];role=roles[u['role']];score=record['performance'];slug=record['slug']
    story=[p('PAGECOM HRIS','HeadingLocal'),p(record['title'],'TitleLocal'),p(f"{record['document_type']} | Recorded {record['uploaded_at'][:10]}",'SmallLocal'),Spacer(1,14)]
    story.append(table([['Staff information','Details'],['Name',u['full_name']],['Employee ID / role',f"{u['employee_id'] or 'Not assigned'} / {role}"],['Department / shift',f"{u['department'] or 'Unassigned'} / {u['work_shift'].title()}"],['Email',u['email']]], [140,371]))
    if slug=='profile':
        story += [p('Professional profile','HeadingLocal'),p(f"{u['full_name']} is registered as {role} in the personnel system. This profile consolidates the current account and work information for internal records."),p('Work focus','HeadingLocal')]
        story += [p('- '+item) for item in responsibilities[u['role']]]
        story += [p('Record maintenance','HeadingLocal'),p('Notify Human Resource when contact details, department assignment or work arrangements change. Qualifications and supporting credentials should be submitted separately when required.')]
    elif slug=='responsibilities':
        story += [p('Responsibilities and reporting','HeadingLocal')]+[p(f'{i+1}. {text}') for i,text in enumerate(responsibilities[u['role']])]
        story += [p('Expected deliverables','HeadingLocal'),p('Maintain a clear activity log, provide evidence for completed work, identify unresolved items and submit a concise progress summary to the assigned reviewer.'),p('Work standards','HeadingLocal'),p('Accuracy, completeness, confidentiality and timely escalation guide the review of assigned responsibilities.')]
    elif slug=='performance':
        story += [p('Performance measures','HeadingLocal')]
        rows=[['Measure','Score','Weight','Contribution']]
        for factor in score['factors'].values():
            value=factor['score'] or 0
            rows.append([factor['label'],f'{value:.1f}%'+(' (no data)' if factor['score'] is None else ''),f"{factor['weight']}%",f"{value*factor['weight']/100:.1f} points"])
        story += [table(rows,[201,110,70,130]),Spacer(1,12),p(f"Overall performance: {score['overall']:.1f}% | {score['label']}"),p(f"Recorded-data coverage: {score['available_weight']}%. Missing measures contribute zero points; weights are not redistributed."),p('Evidence summary','HeadingLocal'),p(f"Attendance: {score['stats']['present']} present, {score['stats']['late']} late, {score['stats']['absent']} absent. Assigned work: {score['stats']['assigned']}; submitted: {score['stats']['submitted']}; rated: {score['stats']['scored']}; overdue pending: {score['stats']['overdue_pending']}."),p('Development priorities','HeadingLocal'),p('Address overdue assignments, strengthen supporting evidence and plan submissions before deadlines. Review changes with the assigned evaluator during the next performance discussion.')]
    elif slug=='training':
        focus={'employee':'Work documentation and customer communication','hr':'Personnel records and leave-review consistency','admin':'Account security and incident documentation'}[u['role']]
        story += [p('Learning objectives','HeadingLocal'),p('Build consistent work habits and address gaps identified in current assignments. Activities below are planned for the next 30 days.'),table([['Activity','Expected evidence'],[focus,'A practical exercise and a short learning reflection.'],['Deadline planning and task tracking','An updated weekly checklist with priority and due date.'],['Data confidentiality and record quality','A reviewed checklist covering access, accuracy and handling.']], [240,271]),p('Follow-up','HeadingLocal'),p('Schedule a progress discussion with Human Resource. Completion dates and supporting evidence will be added after the learning activities are finished.')]
    else:
        story += [p('Onboarding requirements','HeadingLocal'),table([['Requirement','Record status'],['Account and employee identifier','Recorded in the personnel system.'],['Role responsibilities','Responsibility summary attached to the personnel file.'],['Emergency contact details','Not provided in this checklist; staff member must complete.'],['Orientation acknowledgement','Awaiting completed acknowledgement.'],['Department confirmation','Confirm with Human Resource where currently unassigned.']], [240,271]),p('Required follow-up','HeadingLocal'),p('Provide the missing emergency contact information and complete the orientation acknowledgement before resubmitting this checklist. Do not include sensitive contact details in public notes.')]
    story += [p('Document review','HeadingLocal'),p(f"Status: {record['status'].title()}"),p(record['hr_notes'] or 'Awaiting Human Resource review.')]
    if record['reviewer_name']:story.append(p(f"Reviewed by: {record['reviewer_name']} | {record['reviewed_at']}",'SmallLocal'))
    buffer=io.BytesIO();doc=SimpleDocTemplate(buffer,pagesize=A4,leftMargin=42,rightMargin=42,topMargin=34,bottomMargin=54,title=record['title'],author='Pagecom HRIS')
    doc.build(story,onFirstPage=footer,onLaterPages=footer)
    data=buffer.getvalue();reader=PdfReader(io.BytesIO(data));text='\n'.join(page.extract_text() for page in reader.pages)
    if u['full_name'] not in text or not reader.pages:raise ValueError('PDF contents failed validation')
    with target.open('xb') as f:f.write(data)
    pages+=len(reader.pages);created+=1
print(f'Created {created} PDF documents ({pages} pages). Existing files were preserved.')
