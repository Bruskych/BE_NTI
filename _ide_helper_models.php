<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $program_id
 * @property int|null $call_id
 * @property int|null $challenge_id
 * @property int $team_id
 * @property int|null $organization_id
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property numeric|null $total_score
 * @property string|null $decision_comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Call|null $call
 * @property-read \App\Models\Challenge|null $challenge
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Evaluation> $evaluations
 * @property-read int|null $evaluations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationHistory> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationPairingSubmission> $pairingSubmissions
 * @property-read int|null $pairing_submissions_count
 * @property-read \App\Models\Program|null $program
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application draft()
 * @method static \Database\Factories\ApplicationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application submitted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereCallId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereChallengeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereDecisionComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application withoutTrashed()
 */
	class Application extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $application_id
 * @property int $field_id
 * @property string|null $value_text
 * @property array<array-key, mixed>|null $value_json
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\FormField|null $field
 * @method static \Database\Factories\ApplicationAnswerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereValueJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer whereValueText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationAnswer withoutTrashed()
 */
	class ApplicationAnswer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $application_id
 * @property string|null $old_status
 * @property string|null $new_status
 * @property int|null $changed_by
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\User|null $changedBy
 * @method static \Database\Factories\ApplicationHistoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereNewStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory whereOldStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationHistory withoutTrashed()
 */
	class ApplicationHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $application_id
 * @property string $type
 * @property string|null $file_path
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Application|null $application
 * @method static \Database\Factories\ApplicationPairingSubmissionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission withoutTrashed()
 */
	class ApplicationPairingSubmission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $action
 * @property string|null $object_type
 * @property int|null $object_id
 * @property array<array-key, mixed>|null $old_values_json
 * @property array<array-key, mixed>|null $new_values_json
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\AuditEventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereNewValuesJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereObjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereOldValuesJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent withoutTrashed()
 */
	class AuditEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $sender_id
 * @property string|null $target_group
 * @property string|null $subject
 * @property string|null $body
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $recipients
 * @property-read int|null $recipients_count
 * @property-read \App\Models\User|null $sender
 * @method static \Database\Factories\BulkMessageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereTargetGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage withoutTrashed()
 */
	class BulkMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $program_id
 * @property string|null $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property string $status
 * @property numeric|null $budget
 * @property int|null $evaluation_template_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \App\Models\EvaluationTemplate|null $evaluationTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormField> $formFields
 * @property-read int|null $form_fields_count
 * @property-read \App\Models\Program|null $program
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specialization> $specializations
 * @property-read int|null $specializations_count
 * @method static \Database\Factories\CallFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereEvaluationTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call withoutTrashed()
 */
	class Call extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $program_id
 * @property int $organization_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $technical_specification
 * @property numeric|null $budget
 * @property int|null $product_owner_id
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property string $status
 * @property int $max_applications
 * @property int $backlog_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $productOwner
 * @property-read \App\Models\Program|null $program
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specialization> $specializations
 * @property-read int|null $specializations_count
 * @method static \Database\Factories\ChallengeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge visibleTo(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereBacklogOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereMaxApplications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereProductOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereTechnicalSpecification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge withoutTrashed()
 */
	class Challenge extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $mentorship_id
 * @property int|null $mentor_id
 * @property int|null $milestone_id
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $notes
 * @property string|null $recommendations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $mentor
 * @property-read \App\Models\Mentorship|null $mentorship
 * @property-read \App\Models\Milestone|null $milestone
 * @method static \Database\Factories\ConsultationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereMentorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereMentorshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereRecommendations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation withoutTrashed()
 */
	class Consultation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $application_id
 * @property int|null $project_id
 * @property int|null $milestone_id
 * @property string|null $type
 * @property string|null $file_name
 * @property string|null $file_path
 * @property string|null $mime_type
 * @property int|null $size
 * @property int $version
 * @property string $classification
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\Milestone|null $milestone
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Milestone> $milestones
 * @property-read int|null $milestones_count
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\User|null $uploadedBy
 * @method static \Database\Factories\DocumentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withoutTrashed()
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $subject
 * @property string|null $body
 * @property array<array-key, mixed>|null $variables_json
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Database\Factories\EmailTemplateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate whereVariablesJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailTemplate withoutTrashed()
 */
	class EmailTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $application_id
 * @property int|null $evaluator_id
 * @property numeric|null $total_score
 * @property string|null $comment
 * @property string|null $recommendation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\User|null $evaluator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EvaluationScore> $scores
 * @property-read int|null $scores_count
 * @method static \Database\Factories\EvaluationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereEvaluatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereRecommendation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation withoutTrashed()
 */
	class Evaluation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $template_id
 * @property string|null $name
 * @property string|null $description
 * @property numeric|null $weight
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EvaluationScore> $scores
 * @property-read int|null $scores_count
 * @property-read \App\Models\EvaluationTemplate|null $template
 * @method static \Database\Factories\EvaluationCriteriaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria withoutTrashed()
 */
	class EvaluationCriteria extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $evaluation_id
 * @property int $criteria_id
 * @property numeric|null $score
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EvaluationCriteria|null $criteria
 * @property-read \App\Models\Evaluation|null $evaluation
 * @method static \Database\Factories\EvaluationScoreFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereEvaluationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore withoutTrashed()
 */
	class EvaluationScore extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $program_id
 * @property string|null $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Call> $calls
 * @property-read int|null $calls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EvaluationCriteria> $criteria
 * @property-read int|null $criteria_count
 * @property-read \App\Models\Program|null $program
 * @method static \Database\Factories\EvaluationTemplateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate withoutTrashed()
 */
	class EvaluationTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $export_type
 * @property array<array-key, mixed>|null $filters_json
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\ExportsLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereExportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereFiltersJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExportsLog withoutTrashed()
 */
	class ExportsLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $program_id
 * @property int|null $call_id
 * @property string|null $name
 * @property string|null $label
 * @property string|null $type
 * @property bool $required
 * @property array<array-key, mixed>|null $options_json
 * @property string|null $validation_rules
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Call|null $call
 * @property-read \App\Models\Program|null $program
 * @method static \Database\Factories\FormFieldFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereCallId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereOptionsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField whereValidationRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormField withoutTrashed()
 */
	class FormField extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $consent_type
 * @property string|null $version
 * @property \Illuminate\Support\Carbon $accepted_at
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\GdprConsentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereConsentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent withoutTrashed()
 */
	class GdprConsent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $project_id
 * @property int|null $mentor_id
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consultation> $consultations
 * @property-read int|null $consultations_count
 * @property-read \App\Models\User|null $mentor
 * @property-read \App\Models\Project|null $project
 * @method static \Database\Factories\MentorshipFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereMentorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship withoutTrashed()
 */
	class Mentorship extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $project_id
 * @property string|null $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property string|null $status
 * @property int $completion_percentage
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consultation> $consultations
 * @property-read int|null $consultations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\Project|null $project
 * @method static \Database\Factories\MilestoneFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCompletionPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone withoutTrashed()
 */
	class Milestone extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $type
 * @property string $channel
 * @property string|null $title
 * @property string|null $message
 * @property array<array-key, mixed>|null $data_json
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read int|null $team_id
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\NotificationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification forTeam($teamId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification forTeamInvite()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification forUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification unread()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDataJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification withoutTrashed()
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property bool $email_enabled
 * @property bool $system_enabled
 * @property bool $marketing_enabled
 * @property bool $deadline_alerts_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\NotificationPreferenceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereDeadlineAlertsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereEmailEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereMarketingEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereSystemEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference withoutTrashed()
 */
	class NotificationPreference extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $tax_id
 * @property string|null $sector
 * @property string|null $website_link
 * @property string|null $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Challenge> $challenges
 * @property-read int|null $challenges_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $owners
 * @property-read int|null $owners_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Partner> $partner
 * @property-read int|null $partner_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $productOwners
 * @property-read int|null $product_owners_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\OrganizationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereWebsiteLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization withoutTrashed()
 */
	class Organization extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $content
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Database\Factories\PageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withoutTrashed()
 */
	class Page extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $organization_id
 * @property string|null $logo_path
 * @property string|null $website_link
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Organization|null $organization
 * @method static \Database\Factories\PartnerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereWebsiteLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner withoutTrashed()
 */
	class Partner extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $author_id
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $excerpt
 * @property string|null $content
 * @property string|null $featured_image
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $og_image
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $author
 * @method static \Database\Factories\PostFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereFeaturedImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereOgImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post withoutTrashed()
 */
	class Post extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $type
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Call> $calls
 * @property-read int|null $calls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Challenge> $challenges
 * @property-read int|null $challenges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EvaluationTemplate> $evaluationTemplates
 * @property-read int|null $evaluation_templates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormField> $formFields
 * @property-read int|null $form_fields_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Program withoutTrashed()
 */
	class Program extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $application_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property numeric|null $final_score
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Application|null $application
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read mixed $team
 * @property-read \App\Models\Mentorship|null $mentorship
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Milestone> $milestones
 * @property-read int|null $milestones_count
 * @method static \Database\Factories\ProjectFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereFinalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project withoutTrashed()
 */
	class Project extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $type
 * @property array<array-key, mixed>|null $parameters_json
 * @property int|null $generated_by
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $generatedBy
 * @method static \Database\Factories\ReportFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereGeneratedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereParametersJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report withoutTrashed()
 */
	class Report extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Call> $calls
 * @property-read int|null $calls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Challenge> $challenges
 * @property-read int|null $challenges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Specialization withoutTrashed()
 */
	class Specialization extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $study_program
 * @property int|null $year
 * @property array<array-key, mixed>|null $skills_json
 * @property string|null $cv_path
 * @property numeric|null $avg_grade
 * @property bool $has_carried_subjects
 * @property \Illuminate\Support\Carbon|null $eligibility_confirmed_at
 * @property string|null $eligibility_document_path
 * @property string|null $academic_documents_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\StudentProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereAcademicDocumentsPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereAvgGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereCvPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereEligibilityConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereEligibilityDocumentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereHasCarriedSubjects($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereSkillsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereStudyProgram($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentProfile withoutTrashed()
 */
	class StudentProfile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property int|null $leader_id
 * @property string|null $description
 * @property array<array-key, mixed>|null $skills_json
 * @property int|null $capacity
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \App\Models\User|null $leader
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specialization> $specializations
 * @property-read int|null $specializations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereLeaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereSkillsJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team withoutTrashed()
 */
	class Team extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar_path
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Milestone> $approvedMilestones
 * @property-read int|null $approved_milestones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AuditEvent> $auditEvents
 * @property-read int|null $audit_events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BulkMessage> $bulkMessagesSent
 * @property-read int|null $bulk_messages_sent_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consultation> $consultations
 * @property-read int|null $consultations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Evaluation> $evaluations
 * @property-read int|null $evaluations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GdprConsent> $gdprConsents
 * @property-read int|null $gdpr_consents_count
 * @property-read string|null $avatar_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $ledTeams
 * @property-read int|null $led_teams_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mentorship> $mentorships
 * @property-read int|null $mentorships_count
 * @property-read \App\Models\NotificationPreference|null $notificationPreference
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organization> $organizations
 * @property-read int|null $organizations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Post> $posts
 * @property-read int|null $posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\StudentProfile|null $studentProfile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $uploadedDocuments
 * @property-read int|null $uploaded_documents_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User team($teams, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTeam($teams)
 */
	class User extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

