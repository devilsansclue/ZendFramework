<?php

require_once dirname(__FILE__)."/../../../../TestHelper.php";

require_once "University/Entities/Student.php";
require_once "University/Entities/Course.php";
require_once "University/Entities/Professor.php";

abstract class Zend_Entity_DbMapper_IntegrationTest_UniversityTestCase extends Zend_Test_PHPUnit_DatabaseTestCase
{
    /**
     *
     * @var Zend_Entity_Manager
     */
    protected $_entityManager = null;

    public function tearDown()
    {
        $this->_entityManager->close();
        $this->getAdapter()->closeConnection();
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__).'/University/Fixtures/universitySeed.xml');
    }

    public function setUp()
    {
        parent::setUp();

        $path = dirname(__FILE__)."/University/Definitions/";
        $dbAdapter = $this->getAdapter();
        $mf = new Zend_Entity_MetadataFactory_Code($path);
        $this->init($mf);
        $this->_entityManager = new Zend_Entity_Manager(array('adapter' => $dbAdapter, 'metadataFactory' => $mf));
    }


    public function init(Zend_Entity_MetadataFactory_Code $mf)
    {

    }

    /**
     * @dataProvider dataStudents
     * @param int $id
     * @param string $name
     * @param int $studentId
     */
    public function testStudentGetById($id, $name, $studentId)
    {
        $student = $this->_entityManager->load("ZendEntity_Student", $id);
        $this->assertEquals($id, $student->id);
        $this->assertEquals($name, $student->name);
        $this->assertEquals($studentId, $student->studentId);
    }

    static public function dataStudents()
    {
        return array(
            array(1, 'Albert Einstein', '1234'),
            array(2, 'Ludwig von Mises', '1078'),
            array(3, 'Adam Smith', '1776'),
        );
    }

    public function testLoadStudent_NotFoundNull()
    {
        $student = $this->_entityManager->load("ZendEntity_Student", 100, Zend_Entity_Manager::NOTFOUND_NULL);

        $this->assertNull($student);
    }

    public function testLoadStudent_NotFoundException()
    {
        $this->setExpectedException("Zend_Entity_NoResultException");

        $student = $this->_entityManager->load("ZendEntity_Student", 100, Zend_Entity_Manager::NOTFOUND_EXCEPTION);
    }

    /**
     * @dataProvider dataCourses
     * @param int $id
     * @param string $name
     */
    public function testCourseGetById($id, $name, $teacherId)
    {
        $course = $this->_entityManager->load("ZendEntity_Course", $id);
        $this->assertEquals($id, $course->id, "Course Id was expected to be ".$id);
        $this->assertEquals($name, $course->name, "Course Name was expected to be ".$name);
        $this->assertEquals($teacherId, $course->teacher->id, "Teacher Id was expected to be ".$teacherId);
    }

    static public function dataCourses()
    {
        return array(
            array(1, 'Human Action', 1),
            array(2, 'Applied Financial Markets', 2),
        );
    }

    /**
     * @dataProvider dataStudentCurrentCourses
     * @param int $id
     * @param array $courseIds
     */
    public function testStudentCurrentCourses($id, $courseIds)
    {
        $student = $this->_entityManager->load("ZendEntity_Student", $id);
        $this->assertEquals(count($courseIds), count($student->currentCourses));
        foreach($student->currentCourses AS $course) {
            $courseId = array_shift($courseIds);
            $this->assertEquals($courseId, $course->id);
        }
    }

    static public function dataStudentCurrentCourses()
    {
        return array(
            array(1, array(1, 2)),
            array(2, array(1, 3)),
            array(3, array(2)),
        );
    }

    protected function createStudentQueryConditionalOnCourseId($courseId)
    {
        $query = new Zend_Db_Mapper_SqlQueryBuilder($this->_entityManager);
        $query->from("university_students")
            ->with("ZendEntity_Student")
            ->joinInner(
                "university_stu_sem_courses",
                "university_stu_sem_courses.student_id = university_students.student_id"
            )
            ->where("university_stu_sem_courses.course_id = ?")
            ->bindParam(1, $courseId);
        return $query;
    }

    public function testStudentGetResultList_ConditionalOnCourseId()
    {
        $query = $this->createStudentQueryConditionalOnCourseId(1);
        $students = $query->getResultList();

        $this->assertEquals(2, count($students));
        $this->assertEquals(1, $students[0]->id);
        $this->assertEquals(2, $students[1]->id);
    }

    public function testStudentGetSingleResult_ForMultipleResults_ThrowsException()
    {
        $query = $this->createStudentQueryConditionalOnCourseId(1);

        $this->setExpectedException("Zend_Entity_NonUniqueResultException");
        $students = $query->getSingleResult();
    }

    public function testStudentGetSingleResult_ConditionalOnCourseId()
    {
        $query = $this->createStudentQueryConditionalOnCourseId(3);

        $student = $query->getSingleResult();

        $this->assertEquals(2, $student->id);
        $this->assertEquals("Ludwig von Mises", $student->name);
    }

    /**
     * @param array $tables
     * @return Zend_Test_PHPUnit_Db_DataSet_QueryDataSet
     */
    protected function getOrderdQueryDataSet(array $tables)
    {
        $ds = new Zend_Test_PHPUnit_Db_DataSet_QueryDataSet($this->getConnection());
        if(in_array("university_students", $tables)) {
            $ds->addTable("university_students", $this->getAdapter()->select()->from('university_students')->order("student_id"));
        }
        if(in_array("university_stu_sem_courses", $tables)) {
            $ds->addTable("university_stu_sem_courses", $this->getAdapter()->select()->from('university_stu_sem_courses')->order(array("student_id", "course_id")));
        }
        return $ds;
    }

    public function testForExistantStudent_SaveAdditionalCourse()
    {
        $student = $this->_entityManager->load("ZendEntity_Student", 2);
        $course = $this->_entityManager->load("ZendEntity_Course", 2);
        $student->currentCourses[] = $course;

        $this->_entityManager->persist($student);

        $ds = $this->getOrderdQueryDataSet(array("university_stu_sem_courses"));

        $this->assertDataSetsEqual(
            $this->createFlatXMLDataSet(dirname(__FILE__)."/University/Fixtures/ExistingStudentAdditionalCoursesAssertion.xml"),
            $ds
        );
    }

    public function testNewStudent_SaveWithCourses()
    {
        $course = $this->_entityManager->load("ZendEntity_Course", 1);

        $student = new ZendEntity_Student();
        $student->id = 4;
        $student->name = "Friedrich August von Hayek";
        $student->studentId = "9876";
        $student->currentCourses = new Zend_Entity_Collection();
        $student->currentCourses[] = $course;

        $this->_entityManager->persist($student);

        $ds = $this->getOrderdQueryDataSet(array("university_students", "university_stu_sem_courses"));

        $this->assertDataSetsEqual(
            $this->createFlatXMLDataSet(dirname(__FILE__)."/University/Fixtures/NewStudentWithCoursesAssertion.xml"),
            $ds
        );
    }

    public function testExistingStudent_RemoveCourse()
    {
        $student = $this->_entityManager->load("ZendEntity_Student", 2);
        unset($student->currentCourses[1]);

        $this->_entityManager->persist($student);

        $ds = $this->getOrderdQueryDataSet(array("university_students", "university_stu_sem_courses"));

        $this->assertDataSetsEqual(
            $this->createFlatXMLDataSet(dirname(__FILE__)."/University/Fixtures/ExistingStudentRemoveCourseAssertion.xml"),
            $ds
        );
    }

    /**
     * @dataProvider dataProfessors
     */
    public function testGetProfessor($id, $name, $salary, $courseCount)
    {
        $prof = $this->_entityManager->load("ZendEntity_Professor", $id);

        $this->assertType('int', $prof->id);
        $this->assertEquals($id, $prof->id);
        $this->assertEquals($name, $prof->name);
        $this->assertType('int', $prof->salary);
        $this->assertEquals($salary, $prof->salary);
        $this->assertEquals($courseCount, count($prof->teachingCourses));
    }

    static public function dataProfessors()
    {
        return array(
            array("1", "John Stuart Mill", 36000, 1),
            array("2", "Jean Baptist Say", 54000, 2),
        );
    }

    public function testSaveCourseWithBidirectionalCascadingProfessorRelation()
    {
        $course = $this->_entityManager->load("ZendEntity_Course", 1);
        $course->teacher->salary = 123475;
        $this->_entityManager->persist($course);
    }

    public function testSaveProfessorWithBidirectionalCascadingCourseRelation()
    {
        $this->markTestSkipped('Nested Loop Fatal Error!');

        $course = new ZendEntity_Course();
        $course->name = "Foo";

        $professor = $this->_entityManager->load("ZendEntity_Professor", 2);
        $course->teacher = $professor;
        $professor->teachingCourses[] = $course;
        count($professor->teachingCourses);

        $this->_entityManager->persist($professor);
    }

    public function testDeleteStudent()
    {
        $student = $this->_entityManager->load("ZendEntity_Student", 1);
        $this->_entityManager->remove($student);

        $ds = new Zend_Test_PHPUnit_Db_DataSet_QueryDataSet($this->getConnection());
        $ds->addTable("university_students");

        $this->assertDataSetsEqual(
            $this->createFlatXMLDataSet(dirname(__FILE__)."/University/Fixtures/DeleteStudentAssertion.xml"),
            $ds
        );
    }

    public function testDeleteStudentTwice_ThrowsException()
    {
        $student = $this->_entityManager->load("ZendEntity_Student", 1);
        $this->_entityManager->remove($student);

        $this->setExpectedException("Zend_Entity_IllegalStateException");
        $this->_entityManager->remove($student);
    }
}
