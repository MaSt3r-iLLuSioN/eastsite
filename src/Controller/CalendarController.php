<?php

namespace App\Controller;
use App\Service\Breadcrumbs;
use App\Entity\CalendarEntity;
use App\Entity\Event;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Google_Service_Calendar as GoogleCalendarService;
use Google_Service_Calendar_Event as GoogleCalendarEvent;
use Google_Service_Calendar_Calendar as GoogleCalendar;
use Google_Service_Calendar_EventDateTime as GoogleCalendarDateTime;
use Google_Service_Calendar_EventReminder as GoogleCalendarEventReminder;
use Google_Service_Calendar_EventReminders as GoogleCalendarEventReminders;
use Google_Service_Calendar_AclRule as AclRule;
use Google_Service_Calendar_AclRuleScope as AclRuleScope;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Service\Config;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use App\Form\Type\DatePickerType;
use App\Form\Type\ColorPickerType;
class CalendarController extends Controller
{
    //EVENT CRUD FUNCTIONS
    
    /**
     * @Route("/admin/calendar/{calendar}/events", name="adminCalendarEvents")
     */
    public function adminCalendarEvents(CalendarEntity $calendar,  Request $request,Breadcrumbs $breadcrumbs, Config $config)
    {
        $this->denyAccessUnlessGranted('view_admin_calendar_area', null);
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Calendars', '/admin/calendar');
        $breadcrumbs->addBreadcrumb('Calendar: '. $calendar->getTitle(),'/calendar/'.$calendar->getId());
        $breadcrumbs->setActive('Events');
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('calendar/calendar-events-admin.html.twig',array(
            'calendar'=>$calendar
        ));
    }
    
    /**
     * @Route("/admin/calendar/{calendar}/events/add", name="addCalendarEvent")
     */
    public function addCalendarEvent(CalendarEntity $calendar,  Request $request,Breadcrumbs $breadcrumbs, Config $config)
    {
        $this->denyAccessUnlessGranted('add_calendar_event', null);
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Calendars', '/admin/calendar');
        $breadcrumbs->addBreadcrumb('Calendar: '. $calendar->getTitle(),'/calendar/'.$calendar->getId());
        $breadcrumbs->addBreadcrumb('Events','/admin/calendar/'.$calendar->getId() . '/events');
        $breadcrumbs->setActive('Add Event');
        $breadcrumbs->setBreadcrumbs();
        
        $googleClient = $config->getGoogleAccount('http://'.$config->getSiteUrl() . '/admin/calendar/'.$calendar->getId() . '/events/add', $request, [GoogleCalendarService::CALENDAR]);
        $service = new GoogleCalendarService($googleClient);
        
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        
        $usersOptions = array();
        
        foreach($users as $user)
        {
            $usersOptions[$user->getUsername() . ': ' . $user->getEmail()] = $user->getId();
        }
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Event Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('location', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Event Location',
                        'class' => 'form-control',
                        'name' => 'location'
                    )
                ))
                ->add('description', TextareaType::class,array(
                    'attr'=>array(
                        'placeholder'=>'Event Description',
                        'class'=>'form-control',
                        'name'=>'description'
                    )
                ))
                ->add('users', ChoiceType::class,array(
                    'choices'=> $usersOptions,
                    'multiple'=>true,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'users'
                    )
                ))
                ->add('additionalattendees', TextareaType::class, array(
                    'attr' => array(
                        'placeholder'=>'Additional Attendees',
                        'class'=>'form-control',
                        'name'=>'additionalattendees'
                    ),
                    'required'=>false
                ))
                ->add('startdate', DatePickerType::class, array(
                    'placeholder' => array(
                        'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                        'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                    ),
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'id'=>'datepicker',
                    'widget'=>'single_text',
                    'attr' => array(
                        'class' => 'form-control datepicker',
                        'name' => 'startdate',
                    ),
                ))
                ->add('enddate', DatePickerType::class, array(
                    'placeholder' => array(
                        'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                        'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                    ),
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'id'=>'datepicker1',
                    'widget'=>'single_text',
                    'attr' => array(
                        'class' => 'form-control datepicker1',
                        'name' => 'enddate'
                    ),
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $emails = array();
            $attendeeIds = $form->get('users')->getData();
            $additionalUsers = $form->get('additionalattendees')->getData();
            $title = $form->get('title')->getData();
            $description = $form->get('title')->getData();
            $startdate = $form->get('startdate')->getData();
            $enddate = $form->get('enddate')->getData();
            $location = $form->get('location')->getData();
            
            foreach ($attendeeIds as $id)
            {
                $user = $em->getRepository(User::class)->find($id);
                $emails[] = array(
                  'email'=>$user->getEmail()  
                );
            }
            
            if($additionalUsers != null || $additionalUsers != '')
            {
                $additionalEmails = explode(',', $additionalUsers);
                foreach($additionalEmails as $email)
                {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // invalid emailaddress

                        $emails[] = array(
                            'email'=>$email
                        );
                    }
                }
            }
            
            $format = 'Y-m-d\TH:i:s'; //DATE_ATOM
            $endDate = $enddate->format($format) . '-04:00';
            
            $startDate = $startdate->format($format) . '-05:00';
            
            //var_dump($startDate . $endDate);
            //exit();
            
            $googleStartDate = new GoogleCalendarDateTime();
            $googleStartDate->setDateTime($startDate);
            $googleStartDate->setTimeZone('America/Indianapolis');
            $googleEndDate = new GoogleCalendarDateTime();
            $googleEndDate->setDateTime($endDate);
            $googleEndDate->setTimeZone('America/Indianapolis');
            
            $googleEventReminders = new GoogleCalendarEventReminders();
            $googleEventReminders->setUseDefault(false);
            
            //setting up google reminders for all attendees
            $googleEventReminder = new GoogleCalendarEventReminder();
            $googleEventReminder->setMethod('email');
            $googleEventReminder->setMinutes(24 * 60);
            
            $googleEventReminders->setOverrides(array($googleEventReminder));
            
            $googleEvent = new GoogleCalendarEvent();
            $googleEvent->setDescription($description);
            $googleEvent->setSummary($title);
            $googleEvent->setStart($googleStartDate);
            $googleEvent->setEnd($googleEndDate);
            $googleEvent->setLocation($location);
            $googleEvent->setAttendees($emails);
            $googleEvent->setAnyoneCanAddSelf(true);
            $googleEvent->setReminders($googleEventReminders);
            $optParams = array(
                'sendNotifications'=>true,
            );
            $googleEvent = $service->events->insert($calendar->getGoogleid(), $googleEvent, $optParams);
            
            $event = new Event();
            $event->setGoogleid($googleEvent->getId());
            $event->setCalendar($calendar);
            $event->setTitle($title);
            $event->setDescription($description);
            $event->setLocation($location);
            $event->setDateend($enddate);
            $event->setDatestart($startdate);
            $event->setAdditionalAttendees($additionalUsers);
            
            foreach($attendeeIds as $id)
            {
                $user = $em->getRepository(User::class)->find($id);
                $event->addAttendee($user);
            }
            
            $em->persist($event);
            $em->flush();
            
            $this->addFlash('notice', 'Event: '.$event->getTitle().' added successfully!');
            return $this->redirectToRoute('adminCalendarEvents',array('calendar'=>$calendar->getId()));
        }
        
        return $this->render('calendar/add-event.html.twig',array(
            'calendar'=>$calendar,
            'form'=>$form->createView(),
        ));
    }
    
    /**
     * @Route("/admin/calendar/{calendar}/events/{event}/edit", name="editCalendarEvent")
     */
    public function editCalendarEvent(CalendarEntity $calendar, Event $event, Request $request, Breadcrumbs $breadcrumbs, Config $config)
    {
        $this->denyAccessUnlessGranted('edit_calendar_event', null);
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Calendars', '/admin/calendar');
        $breadcrumbs->addBreadcrumb('Calendar: '. $calendar->getTitle(),'/calendar/'.$calendar->getId());
        $breadcrumbs->addBreadcrumb('Events','/admin/calendar/'.$calendar->getId() . '/events');
        $breadcrumbs->addBreadcrumb($event->getTitle(),'/admin/calendar/'.$calendar->getId() . '/events/'.$event->getId());
        $breadcrumbs->setActive('Edit Event');
        $breadcrumbs->setBreadcrumbs();
        
        $googleClient = $config->getGoogleAccount('http://'.$config->getSiteUrl() . '/admin/calendar/'.$calendar->getId() . '/events/add', $request, [GoogleCalendarService::CALENDAR]);
        $service = new GoogleCalendarService($googleClient);
        
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        
        $usersOptions = array();
        $selectedUsers = array();
        foreach($event->getAttendees() as $attendee)
        {
            $selectedUsers[] = $attendee->getId();
        }
        foreach($users as $user)
        {
            $usersOptions[$user->getUsername() . ': ' . $user->getEmail()] = $user->getId();
        }
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'data'=>$event->getTitle(),
                    'attr' => array(
                        'placeholder' => 'Event Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('location', TextType::class, array(
                    'data'=>$event->getLocation(),
                    'attr' => array(
                        'placeholder' => 'Event Location',
                        'class' => 'form-control',
                        'name' => 'location'
                    )
                ))
                ->add('description', TextareaType::class,array(
                    'data'=>$event->getDescription(),
                    'attr'=>array(
                        'placeholder'=>'Event Description',
                        'class'=>'form-control',
                        'name'=>'description'
                    )
                ))
                ->add('users', ChoiceType::class,array(
                    'data'=>$selectedUsers,
                    'choices'=> $usersOptions,
                    'multiple'=>true,
                    'attr' => array(
                        'class' => 'form-control',
                        'name' => 'users'
                    )
                ))
                ->add('additionalattendees', TextareaType::class, array(
                    'data'=>$event->getAdditionalattendees(),
                    'attr' => array(
                        'placeholder'=>'Additional Attendees',
                        'class'=>'form-control',
                        'name'=>'additionalattendees'
                    ),
                    'required'=>false
                ))
                ->add('startdate', DatePickerType::class, array(
                    'data'=>$event->getDatestart(),
                    'defaultDate'=>$event->getDatestart(),
                    'placeholder' => array(
                        'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                        'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                    ),
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'id'=>'datepicker',
                    'widget'=>'single_text',
                    'attr' => array(
                        'class' => 'form-control datepicker',
                        'name' => 'startdate',
                    ),
                ))
                ->add('enddate', DatePickerType::class, array(
                    'data'=>$event->getDateend(),
                    'defaultDate'=>$event->getDateend(),
                    'placeholder' => array(
                        'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                        'hour' => 'Hour', 'minute' => 'Minute', 'second' => 'Second',
                    ),
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'id'=>'datepicker1',
                    'widget'=>'single_text',
                    'attr' => array(
                        'class' => 'form-control datepicker1',
                        'name' => 'enddate'
                    ),
                ))
                
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $emails = array();
            $attendeeIds = $form->get('users')->getData();
            $additionalUsers = $form->get('additionalattendees')->getData();
            $title = $form->get('title')->getData();
            $description = $form->get('title')->getData();
            $startdate = $form->get('startdate')->getData();
            $enddate = $form->get('enddate')->getData();
            $location = $form->get('location')->getData();
            
            foreach ($attendeeIds as $id)
            {
                $user = $em->getRepository(User::class)->find($id);
                $emails[] = array(
                  'email'=>$user->getEmail()  
                );
            }
            
            if($additionalUsers != null || $additionalUsers != '')
            {
                $additionalEmails = explode(',', $additionalUsers);
                foreach($additionalEmails as $email)
                {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // invalid emailaddress

                        $emails[] = array(
                            'email'=>$email
                        );
                    }
                }
            }
            
            $format = 'Y-m-d\TH:i:s'; //DATE_ATOM
            $endDate = $enddate->format($format) . '-04:00';
            
            $startDate = $startdate->format($format) . '-05:00';
            
            //var_dump($startDate . $endDate);
            //exit();
            
            $googleStartDate = new GoogleCalendarDateTime();
            $googleStartDate->setDateTime($startDate);
            $googleStartDate->setTimeZone('America/Indianapolis');
            $googleEndDate = new GoogleCalendarDateTime();
            $googleEndDate->setDateTime($endDate);
            $googleEndDate->setTimeZone('America/Indianapolis');
            
            $googleEventReminders = new GoogleCalendarEventReminders();
            $googleEventReminders->setUseDefault(false);
            
            //setting up google reminders for all attendees
            $googleEventReminder = new GoogleCalendarEventReminder();
            $googleEventReminder->setMethod('email');
            $googleEventReminder->setMinutes(24 * 60);
            
            $googleEventReminders->setOverrides(array($googleEventReminder));
            
            $googleEvent = $service->events->get($calendar->getGoogleid(), $event->getGoogleid());
            $googleEvent->setDescription($description);
            $googleEvent->setSummary($title);
            $googleEvent->setStart($googleStartDate);
            $googleEvent->setEnd($googleEndDate);
            $googleEvent->setLocation($location);
            $googleEvent->setAttendees($emails);
            $googleEvent->setAnyoneCanAddSelf(true);
            $googleEvent->setReminders($googleEventReminders);
            $optParams = array(
                'sendNotifications'=>true,
            );
            $googleEvent = $service->events->update($calendar->getGoogleid(), $googleEvent->getId(), $googleEvent, $optParams);
            
            $event->setGoogleid($googleEvent->getId());
            $event->setCalendar($calendar);
            $event->setTitle($title);
            $event->setDescription($description);
            $event->setLocation($location);
            $event->setDateend($enddate);
            $event->setDatestart($startdate);
            $event->setAdditionalAttendees($additionalUsers);
            
            //remove all old attendees
            $attendees = $event->getAttendees();
            foreach($attendees as $attendee)
            {
                $event->removeAttendee($attendee);
            }
            
            foreach($attendeeIds as $uid)
            {
                
                $user = $em->getRepository(User::class)->find($uid);
                $event->addAttendee($user);
            }
            
            $em->persist($event);
            $em->flush();
            
            $this->addFlash('notice', 'Event: '.$event->getTitle().' edited successfully!');
            return $this->redirectToRoute('adminCalendarEvents',array('calendar'=>$calendar->getId()));
        }
        
        return $this->render('calendar/edit-event.html.twig',array(
            'calendar'=>$calendar,
            'event'=>$event,
            'form'=>$form->createView(),
        ));
    }
    
    /**
     * @Route("/admin/calendar/{calendar}/events/{event}/delete", name="deleteCalendarEvent")
     */
    public function deleteCalendarEvent(CalendarEntity $calendar, Event $event, Request $request,Breadcrumbs $breadcrumbs, Config $config)
    {
        $this->denyAccessUnlessGranted('delete_calendar_event', null);
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Calendars', '/admin/calendar');
        $breadcrumbs->addBreadcrumb('Calendar: '. $calendar->getTitle(),'/calendar/'.$calendar->getId());
        $breadcrumbs->addBreadcrumb('Events','/admin/calendar/'.$calendar->getId() . '/events');
        $breadcrumbs->addBreadcrumb($event->getTitle(),'/admin/calendar/'.$calendar->getId() . '/events/'.$event->getId());
        $breadcrumbs->setActive('Delete Event');
        $breadcrumbs->setBreadcrumbs();
        
        $googleClient = $config->getGoogleAccount('http://' . $config->getSiteUrl() . '/admin/' . $calendar->getId() . '/delete', $request, [GoogleCalendarService::CALENDAR]);
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('hidden', HiddenType::class, array(
                    
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            //remove the calendar from google
            
            $service = new GoogleCalendarService($googleClient);
            $service->events->delete($calendar->getGoogleid(),$event->getGoogleid());
            
            $em = $this->getDoctrine()->getManager();
            
            //remove event from calendar
            $calendar->removeEvent($event);
            $em->remove($event);
            $em->flush();
            $this->addFlash('notice', 'Calendar Event: '.$event->getTitle().' deleted successfully!');
            return $this->redirectToRoute('adminViewEvent', array('calendar'=>$calendar, 'event'=>$event));
        }
        
        return $this->render('calendar/delete-event.html.twig',array(
            'calendar'=>$calendar,
            'event'=>$event,
            'form'=>$form->createView()
        ));
    }
    
    /**
     * @Route("/admin/calendar/{calendar}/events/{event}", name="adminViewEvent")
     */
    public function adminViewEvent(CalendarEntity $calendar, Event $event, Request $request,Breadcrumbs $breadcrumbs, Config $config)
    {
        $this->denyAccessUnlessGranted('view_admin_calendar_area', null);
        
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Calendars', '/admin/calendar');
        $breadcrumbs->addBreadcrumb('Calendar: '. $calendar->getTitle(),'/calendar/'.$calendar->getId());
        $breadcrumbs->addBreadcrumb('Events','/admin/calendar/'.$calendar->getId() . '/events');
        $breadcrumbs->setActive($event->getTitle());
        $breadcrumbs->setBreadcrumbs();
        
        return $this->render('calendar/calendar-event-admin.html.twig',array(
            'calendar'=>$calendar,
            'event'=>$event,
        ));
    }
    
    
    //CALENDAR CRUD FUNCTIONS
    
    /**
     * @Route("/admin/calendar/add", name="addCalendar")
     */
    public function addCalendar(Request $request,Breadcrumbs $breadcrumbs, Config $config)
    {
        $this->denyAccessUnlessGranted('add_calendar', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Calendars', '/admin/calendar');
        $breadcrumbs->setActive('Add Calendar');
        $breadcrumbs->setBreadcrumbs();
        
        //setup google client 
        $googleClient = $config->getGoogleAccount('http://'.$config->getSiteUrl() . '/admin/calendar/add',$request, [GoogleCalendarService::CALENDAR]);
        $service = new GoogleCalendarService($googleClient);
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Calendar Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    )
                ))
                ->add('description', TextareaType::class,array(
                    'attr'=>array(
                        'placeholder'=>'Calendar Description',
                        'class'=>'form-control',
                        'name'=>'description'
                    )
                ))
                ->add('public', ChoiceType::class,array(
                    'choices'=>array(
                        'Yes'=>true,
                        'No'=>false
                    ),
                    'attr'=>array(
                        'class'=>'form-control',
                        'name'=>'public'
                    )
                ))
                ->add('color', ColorPickerType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'id'=>'color',
                    'attr' => array(
                        'class' => 'form-control color',
                        'name' => 'color'
                    ),
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $public = $form->get('public')->getData();
            $googlecalendar = new GoogleCalendar();
            //summary is calendar title
            $googlecalendar->setSummary($form->get('title')->getData());
            $googlecalendar->setDescription($form->get('description')->getData());
            $googlecalendar->setTimeZone('America/Indianapolis');
            
            $googlecalendar = $service->calendars->insert($googlecalendar);
            
            if($public == true)
            {
                $rule = new AclRule();
                $scope = new AclRuleScope();

                $scope->setType("default");
                $scope->setValue("");
                $rule->setScope($scope);
                $rule->setRole("reader");

                $createdRule = $service->acl->insert($googlecalendar->getId(), $rule);
            }
            $calendar = new CalendarEntity();
            $calendar->setPublic($public);
            $calendar->setTitle($form->get('title')->getData());
            $calendar->setGoogleid($googlecalendar->getId());
            $calendar->setDescription($form->get('description')->getData());
            $calendar->setColor($form->get('color')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($calendar);
            $em->flush();
            $this->addFlash('notice', 'Calendar: '.$calendar->getTitle().' added successfully!');
            return $this->redirectToRoute('viewCalendars');
        }
        return $this->render('calendar/add-calendar.html.twig',array(
            'form'=>$form->createView()
        ));
    }
    /**
     * @Route("/admin/calendar/{calendar}/edit", name="editCalendar")
     */
    public function editCalendar(CalendarEntity $calendar,  Request $request,Breadcrumbs $breadcrumbs, Config $config)
    {
        $this->denyAccessUnlessGranted('edit_calendar', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Calendars', '/admin/calendar');
        $breadcrumbs->addBreadcrumb('Calendar: '. $calendar->getTitle(),'/calendar/'.$calendar->getId());
        $breadcrumbs->setActive('Edit Calendar');
        $breadcrumbs->setBreadcrumbs();
        
        $googleClient = $config->getGoogleAccount('http://'.$config->getSiteUrl() . '/admin/calendar/add',$request, [GoogleCalendarService::CALENDAR]);
        $service = new GoogleCalendarService($googleClient);
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('title', TextType::class, array(
                    'attr' => array(
                        'placeholder' => 'Calendar Title',
                        'class' => 'form-control',
                        'name' => 'title'
                    ),
                    'data'=>$calendar->getTitle(),
                ))
                ->add('description', TextareaType::class,array(
                    'attr'=>array(
                        'placeholder'=>'Calendar Description',
                        'class'=>'form-control',
                        'name'=>'description'
                    ),
                    'data'=>$calendar->getDescription(),
                ))
                ->add('public', ChoiceType::class,array(
                    'data'=>$calendar->getPublic(),
                    'choices'=>array(
                        'Yes'=>true,
                        'No'=>false
                    ),
                    'attr'=>array(
                        'class'=>'form-control',
                        'name'=>'public'
                    )
                ))
                ->add('color', ColorPickerType::class, array(
                    'twig'=>$this->container->get('twig'),
                    'dispatcher'=>$this->container->get('event_dispatcher'),
                    'id'=>'color',                       
                    'attr' => array(
                        'class' => 'form-control color',
                        'name' => 'color'
                    ),
                    'data'=>$calendar->getColor(),
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $public = $form->get('public')->getData();
            
            $googlecalendar = $service->calendars->get($calendar->getGoogleid());
            //summary is calendar title
            $googlecalendar->setSummary($form->get('title')->getData());
            $googlecalendar->setDescription($form->get('description')->getData());
            $googlecalendar = $service->calendars->update($calendar->getGoogleid(),$googlecalendar);
            
            if($public == true)
            {
                $rule = new AclRule();
                $scope = new AclRuleScope();

                $scope->setType("default");
                $scope->setValue("");
                $rule->setScope($scope);
                $rule->setRole("reader");

                $createdRule = $service->acl->insert($googlecalendar->getId(), $rule);
            }
            else
            {
                $rule = new AclRule();
                $scope = new AclRuleScope();

                $scope->setType("default");
                $scope->setValue("");
                $rule->setScope($scope);
                $rule->setRole("none");

                $createdRule = $service->acl->insert($googlecalendar->getId(), $rule);
            }
            
            $calendar->setTitle($form->get('title')->getData());
            $calendar->setGoogleid($googlecalendar->getId());
            $calendar->setColor($form->get('color')->getData());
            $calendar->setDescription($form->get('description')->getData());
            $calendar->setPublic($public);
            $em = $this->getDoctrine()->getManager();
            $em->persist($calendar);
            $em->flush();
            $this->addFlash('notice', 'Calendar: '.$calendar->getTitle().' edited successfully!');
            return $this->redirectToRoute('viewCalendars');
        }
        return $this->render('calendar/edit-calendar.html.twig',array(
            'calendar'=>$calendar,
            'form'=>$form->createView()
        ));
        
    }
    /**
     * @Route("/admin/calendar/{calendar}/delete", name="deleteCalendar")
     */
    public function deleteCalendar(CalendarEntity $calendar,  Request $request,Breadcrumbs $breadcrumbs, Config $config)
    {
        $this->denyAccessUnlessGranted('delete_calendar', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->addBreadcrumb('Calendars', '/admin/calendar');
        $breadcrumbs->addBreadcrumb('Calendar: '. $calendar->getTitle(),'/calendar/'.$calendar->getId());
        $breadcrumbs->setActive('Delete Calendar');
        $breadcrumbs->setBreadcrumbs();
        
        $formFactory = Forms::createFormFactoryBuilder()
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        $form = $formFactory->createBuilder()
                ->add('hidden', HiddenType::class, array(
                    
                ))
                ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            //remove the calendar from google
            $googleClient = $config->getGoogleAccount('http://' . $config->getSiteUrl() . '/admin/' . $calendar->getId() . '/delete', $request, [GoogleCalendarService::CALENDAR]);
            
            $service = new GoogleCalendarService($googleClient);
            $service->calendars->delete($calendar->getGoogleid());
            
            $em = $this->getDoctrine()->getManager();
            
            //remove all events for this calendar
            $events = $calendar->getEvents();
            foreach($events as $event)
            {
                $calendar->removeEvent($event);
                $em->remove($event);
                $em->flush();
            }
            
            $em->remove($calendar);
            $em->flush();
            $this->addFlash('notice', 'Calendar: '.$calendar->getTitle().' deleted successfully!');
            return $this->redirectToRoute('viewCalendars');
        }
        return $this->render('calendar/delete-calendar.html.twig',array(
            'form'=>$form->createView(),
            'calendar'=>$calendar
        ));
                
    }
    
    /**
     * @Route("/admin/calendar", name="viewCalendars")
     */
    public function viewCalendars(Request $request,Breadcrumbs $breadcrumbs)
    {
        $this->denyAccessUnlessGranted('view_admin_calendar_area', null);
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->addBreadcrumb('Account', '/account');
        $breadcrumbs->addBreadcrumb('Admin Dashoard', '/admin');
        $breadcrumbs->setActive('Calendars');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        $calendars = $em->getRepository(CalendarEntity::class)->findAll();
        
        return $this->render('calendar/view-calendars-admin.html.twig',array(
            'calendars'=>$calendars
        ));
        
    }
    
    
    /**
     * @Route("/calendar", name="viewCalendar")
     */
    public function viewCalendar(Request $request,Breadcrumbs $breadcrumbs)
    {
        //setup breadcrumbs
        $breadcrumbs->addBreadcrumb('Home', '/');
        $breadcrumbs->setActive('Calendars');
        $breadcrumbs->setBreadcrumbs();
        
        $em = $this->getDoctrine()->getManager();
        
        $calendars = $em->getRepository(CalendarEntity::class)->findAll(); 
        return $this->render('calendar/view-calendar.html.twig',array(
            'calendars'=>$calendars
        ));
    }
    
}