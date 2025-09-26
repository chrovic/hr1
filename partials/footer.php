<footer class="footer bg-white border-top">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <div class="py-3">
                    <div class="small mb-2">
                        <span>&copy; <?php echo date('Y'); ?> HR1 Human Resources Management System. All rights reserved.</span>
                    </div>
                    <div class="small">
                        <a href="#" data-toggle="modal" data-target="#termsModal">
                            Terms & Conditions
                        </a>
                        <span class="mx-2">|</span>
                        <a href="#" data-toggle="modal" data-target="#privacyModal">
                            Privacy Policy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="terms-content">
                    <h6 class="text-primary mb-3">HR1 Human Resources Management System - Terms and Conditions</h6>
                    
                    <p class="text-muted small mb-4">
                        <strong>Effective Date:</strong> <?php echo date('F d, Y'); ?><br>
                        <strong>Last Updated:</strong> <?php echo date('F d, Y'); ?>
                    </p>

                    <div class="mb-4">
                        <h6 class="text-dark">1. Acceptance of Terms</h6>
                        <p class="text-justify small">
                            By accessing and using the HR1 Human Resources Management System, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. These terms are governed by the laws of the Republic of the Philippines and are subject to the provisions of Republic Act No. 10173 (Data Privacy Act of 2012), Republic Act No. 10175 (Cybercrime Prevention Act of 2012), and other applicable Philippine laws.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">2. Data Privacy and Protection</h6>
                        <p class="text-justify small">
                            In compliance with <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong>, we are committed to protecting your personal information. This system collects, processes, and stores employee data including but not limited to:
                        </p>
                        <ul class="small">
                            <li>Personal identification information</li>
                            <li>Employment records and performance data</li>
                            <li>Training and competency assessments</li>
                            <li>System usage logs and activities</li>
                        </ul>
                        <p class="text-justify small">
                            All data processing activities are conducted in accordance with the principles of transparency, legitimate purpose, and proportionality as mandated by the Data Privacy Act.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">3. User Responsibilities and Prohibited Activities</h6>
                        <p class="text-justify small">
                            In accordance with <strong>Republic Act No. 10175 (Cybercrime Prevention Act of 2012)</strong>, users are strictly prohibited from:
                        </p>
                        <ul class="small">
                            <li>Unauthorized access to system resources or other users' data</li>
                            <li>Interfering with system operations or data integrity</li>
                            <li>Distributing malicious software or engaging in cyber attacks</li>
                            <li>Sharing login credentials or allowing unauthorized access</li>
                            <li>Using the system for any illegal or unauthorized purposes</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">4. Electronic Transactions and Signatures</h6>
                        <p class="text-justify small">
                            Pursuant to <strong>Republic Act No. 8792 (Electronic Commerce Act of 2000)</strong>, electronic documents, records, and signatures generated within this system have the same legal effect as their paper-based counterparts. Users consent to the electronic processing of HR transactions and acknowledge the validity of digital signatures and electronic approvals.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">5. System Access and Security</h6>
                        <p class="text-justify small">
                            Access to this system is granted based on your role and organizational requirements. You are responsible for:
                        </p>
                        <ul class="small">
                            <li>Maintaining the confidentiality of your login credentials</li>
                            <li>Reporting any security breaches or suspicious activities</li>
                            <li>Using the system only for authorized business purposes</li>
                            <li>Complying with all applicable company policies and procedures</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">6. Data Retention and Disposal</h6>
                        <p class="text-justify small">
                            Employee data will be retained in accordance with applicable Philippine labor laws and company policies. Upon termination of employment or as required by law, data will be disposed of securely in compliance with the Data Privacy Act's requirements for data disposal.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">7. Limitation of Liability</h6>
                        <p class="text-justify small">
                            The system is provided "as is" without warranties of any kind. The organization shall not be liable for any indirect, incidental, or consequential damages arising from the use of this system, except as required by applicable Philippine law.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">8. Governing Law and Jurisdiction</h6>
                        <p class="text-justify small">
                            These Terms and Conditions are governed by the laws of the Republic of the Philippines. Any disputes arising from the use of this system shall be subject to the exclusive jurisdiction of the Philippine courts.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">9. Contact Information</h6>
                        <p class="text-justify small">
                            For questions regarding these Terms and Conditions or data privacy concerns, please contact the HR Department or the Data Protection Officer at your organization.
                        </p>
                    </div>

                    <div class="alert alert-info small">
                        <strong>Note:</strong> These Terms and Conditions may be updated periodically to reflect changes in applicable laws or system functionality. Users will be notified of any material changes through the system or other appropriate means.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" role="dialog" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="privacy-content">
                    <h6 class="text-primary mb-3">HR1 Human Resources Management System - Privacy Policy</h6>
                    
                    <p class="text-muted small mb-4">
                        <strong>Effective Date:</strong> <?php echo date('F d, Y'); ?><br>
                        <strong>Last Updated:</strong> <?php echo date('F d, Y'); ?>
                    </p>

                    <div class="mb-4">
                        <h6 class="text-dark">Our Commitment to Privacy</h6>
                        <p class="text-justify small">
                            This Privacy Policy explains how we collect, use, disclose, and protect your personal information in compliance with <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong> and other applicable Philippine laws.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">Information We Collect</h6>
                        <p class="text-justify small">We collect the following types of personal information:</p>
                        <ul class="small">
                            <li><strong>Personal Information:</strong> Name, employee ID, contact details, and identification documents</li>
                            <li><strong>Employment Information:</strong> Job title, department, employment history, and performance records</li>
                            <li><strong>System Usage Data:</strong> Login times, system activities, and user interactions</li>
                            <li><strong>Training Records:</strong> Competency assessments, training history, and development plans</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">How We Use Your Information</h6>
                        <p class="text-justify small">We use your personal information for:</p>
                        <ul class="small">
                            <li>Human resources management and administration</li>
                            <li>Performance evaluation and competency assessment</li>
                            <li>Training and development planning</li>
                            <li>Compliance with legal and regulatory requirements</li>
                            <li>System security and fraud prevention</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">Data Security</h6>
                        <p class="text-justify small">
                            We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction, in accordance with the Data Privacy Act's security requirements.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">Your Rights</h6>
                        <p class="text-justify small">Under the Data Privacy Act, you have the right to:</p>
                        <ul class="small">
                            <li>Be informed about the processing of your personal data</li>
                            <li>Access your personal data and request corrections</li>
                            <li>Object to the processing of your personal data</li>
                            <li>Request the erasure or blocking of your personal data</li>
                            <li>Data portability and damages for violations</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-dark">Data Sharing and Disclosure</h6>
                        <p class="text-justify small">
                            We may share your personal information only with authorized personnel within the organization and third parties as required by law or with your explicit consent. We do not sell or rent your personal information to third parties.
                        </p>
                    </div>

                    <div class="alert alert-warning small">
                        <strong>Important:</strong> This Privacy Policy is subject to the provisions of Republic Act No. 10173 and other applicable Philippine laws. For any privacy concerns or to exercise your rights, please contact our Data Protection Officer.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
