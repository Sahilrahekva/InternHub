-- CREATE DATABASE IF NOT EXISTS internships_db;
-- USE internships_db;

-- CREATE TABLE IF NOT EXISTS programs (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     company VARCHAR(100) NOT NULL,
--     type ENUM('internship', 'certification') NOT NULL,
--     title VARCHAR(200) NOT NULL,
--     description TEXT,
--     start_date DATE NOT NULL,
--     end_date DATE NOT NULL,
--     status VARCHAR(50) DEFAULT 'active',  -- Can be 'launching', 'active', 'expired'
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- -- Sample data for top MNCs (simulated current programs)
-- INSERT INTO programs (company, type, title, description, start_date, end_date, status) VALUES
-- ('Google', 'internship', 'Google Summer Internship 2024', 'Hands-on experience in AI and cloud computing. Open to undergrads.', '2024-06-01', '2024-08-31', 'active',"www.google.com"),
-- ('Google', 'certification', 'Google Data Analytics Certificate', 'Free course on Coursera covering data skills. Launching updates now.', '2024-05-20', '2024-12-31', 'launching'),
-- ('Microsoft', 'internship', 'Microsoft Explore Program', 'Internship in software engineering. Virtual option available.', '2024-07-01', '2024-09-30', 'launching'),
-- ('Microsoft', 'certification', 'Microsoft Azure Fundamentals (Free)', 'Intro to cloud computing. Self-paced and free.', '2024-04-01', '2024-12-31', 'active'),
-- ('Amazon', 'internship', 'Amazon SDE Intern', 'Software development internship with real projects.', '2024-06-15', '2024-09-15', 'active'),
-- ('Amazon', 'certification', 'AWS Cloud Practitioner (Free Tier)', 'Free certification for cloud basics. New modules launching.', '2024-05-25', '2024-11-30', 'launching'),
-- ('IBM', 'internship', 'IBM Tech Internship', 'Focus on AI and data science. Global opportunities.', '2024-07-10', '2024-10-10', 'launching'),
-- ('IBM', 'certification', 'IBM Data Science Professional Certificate', 'Free on Coursera with hands-on labs.', '2024-03-01', '2024-12-31', 'active'),
-- ('Meta', 'internship', 'Meta University Internship', 'Product and engineering roles. Apply now for fall.', '2024-08-01', '2024-11-30', 'active'),
-- ('Meta', 'certification', 'Meta Front-End Developer Certificate', 'Free professional certificate program.', '2024-06-05', '2024-12-31', 'launching'),
-- ('Apple', 'internship', 'Apple Software Engineering Intern', 'Innovative projects in iOS development.', '2024-07-15', '2024-10-15', 'active'),
-- ('Apple', 'certification', 'Apple Developer Academy (Free)', 'Free training for app developers.', '2024-05-01', '2024-11-01', 'active'),
-- ('Oracle', 'internship', 'Oracle Cloud Internship', 'Cloud and database experience.', '2024-06-20', '2024-09-20', 'launching'),
-- ('Oracle', 'certification', 'Oracle Cloud Infrastructure Foundations (Free)', 'Intro cert with free access.', '2024-04-15', '2024-10-31', 'active'),
-- ('Cisco', 'internship', 'Cisco Networking Intern', 'Cybersecurity and networking focus.', '2024-07-05', '2024-10-05', 'active'),
-- ('Cisco', 'certification', 'Cisco Certified Support Technician (Free)', 'Entry-level free cert.', '2024-05-10', '2024-12-10', 'launching'),
-- ('Intel', 'internship', 'Intel Hardware Engineering Intern', 'Chip design and AI hardware.', '2024-06-10', '2024-09-10', 'active'),
-- ('Intel', 'certification', 'Intel AI Fundamentals (Free)', 'Free course on AI edge computing.', '2024-04-20', '2024-11-20', 'active'),
-- ('Samsung', 'internship', 'Samsung R&D Internship', 'Mobile and semiconductor tech.', '2024-07-20', '2024-10-20', 'launching'),
-- ('Samsung', 'certification', 'Samsung Knox Security (Free Training)', 'Free security certification.', '2024-05-15', '2024-12-15', 'active');






CREATE DATABASE IF NOT EXISTS internships_db;
USE internships_db;

CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company VARCHAR(100) NOT NULL,
    type ENUM('internship', 'certification') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'active',  -- Can be 'launching', 'active', 'expired'
    link VARCHAR(255),  -- New column for program links (e.g., application or info URLs)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data for top MNCs (simulated current programs) with added links
INSERT INTO programs (company, type, title, description, start_date, end_date, status, link) VALUES
('Google', 'internship', 'Google Summer Internship 2024', 'Hands-on experience in AI and cloud computing. Open to undergrads.', '2024-06-01', '2024-08-31', 'active', 'https://careers.google.com/students/'),
('Google', 'certification', 'Google Data Analytics Certificate', 'Free course on Coursera covering data skills. Launching updates now.', '2024-05-20', '2024-12-31', 'launching', 'https://www.coursera.org/professional-certificates/google-data-analytics'),
('Microsoft', 'internship', 'Microsoft Explore Program', 'Internship in software engineering. Virtual option available.', '2024-07-01', '2024-09-30', 'launching', 'https://careers.microsoft.com/students/us/en'),
('Microsoft', 'certification', 'Microsoft Azure Fundamentals (Free)', 'Intro to cloud computing. Self-paced and free.', '2024-04-01', '2024-12-31', 'active', 'https://learn.microsoft.com/en-us/certifications/azure-fundamentals/'),
('Amazon', 'internship', 'Amazon SDE Intern', 'Software development internship with real projects.', '2024-06-15', '2024-09-15', 'active', 'https://www.amazon.jobs/en/teams/internships-for-students'),
('Amazon', 'certification', 'AWS Cloud Practitioner (Free Tier)', 'Free certification for cloud basics. New modules launching.', '2024-05-25', '2024-11-30', 'launching', 'https://aws.amazon.com/certification/certified-cloud-practitioner/'),
('IBM', 'internship', 'IBM Tech Internship', 'Focus on AI and data science. Global opportunities.', '2024-07-10', '2024-10-10', 'launching', 'https://www.ibm.com/careers/students'),
('IBM', 'certification', 'IBM Data Science Professional Certificate', 'Free on Coursera with hands-on labs.', '2024-03-01', '2024-12-31', 'active', 'https://www.coursera.org/professional-certificates/ibm-data-science'),
('Meta', 'internship', 'Meta University Internship', 'Product and engineering roles. Apply now for fall.', '2024-08-01', '2024-11-30', 'active', 'https://www.metacareers.com/students/'),
('Meta', 'certification', 'Meta Front-End Developer Certificate', 'Free professional certificate program.', '2024-06-05', '2024-12-31', 'launching', 'https://www.coursera.org/professional-certificates/meta-front-end-developer'),
('Apple', 'internship', 'Apple Software Engineering Intern', 'Innovative projects in iOS development.', '2024-07-15', '2024-10-15', 'active', 'https://www.apple.com/careers/us/students.html'),
('Apple', 'certification', 'Apple Developer Academy (Free)', 'Free training for app developers.', '2024-05-01', '2024-11-01', 'active', 'https://developer.apple.com/programs/'),
('Oracle', 'internship', 'Oracle Cloud Internship', 'Cloud and database experience.', '2024-06-20', '2024-09-20', 'launching', 'https://www.oracle.com/corporate/careers/students/'),
('Oracle', 'certification', 'Oracle Cloud Infrastructure Foundations (Free)', 'Intro cert with free access.', '2024-04-15', '2024-10-31', 'active', 'https://education.oracle.com/oracle-cloud-infrastructure-foundations/'),
('Cisco', 'internship', 'Cisco Networking Intern', 'Cybersecurity and networking focus.', '2024-07-05', '2024-10-05', 'active', 'https://jobs.cisco.com/jobs/SearchJobs/internship'),
('Cisco', 'certification', 'Cisco Certified Support Technician (Free)', 'Entry-level free cert.', '2024-05-10', '2024-12-10', 'launching', 'https://learningnetwork.cisco.com/s/learning-plan-detail-standard?ltui__urlRecordId=a1c3i0000005uz2AAA&ltui__urlRedirect=learning-plan-detail-standard'),
('Intel', 'internship', 'Intel Hardware Engineering Intern', 'Chip design and AI hardware.', '2024-06-10', '2024-09-10', 'active', 'https://jobs.intel.com/en/students'),
('Intel', 'certification', 'Intel AI Fundamentals (Free)', 'Free course on AI edge computing.', '2024-04-20', '2024-11-20', 'active', 'https://www.intel.com/content/www/us/en/developer/topic-technology/artificial-intelligence/training/overview.html'),
('Samsung', 'internship', 'Samsung R&D Internship', 'Mobile and semiconductor tech.', '2024-07-20', '2024-10-20', 'launching', 'https://www.samsungcareers.com/us/students'),
('Samsung', 'certification', 'Samsung Knox Security (Free Training)', 'Free security certification.', '2024-05-15', '2024-12-15', 'active', 'https://www.samsungknox.com/en/solutions/enterprise-security-certification');